<?php

namespace App\Command;

use App\Service\Chat;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
# deprecated
#use React\EventLoop\Factory;
#use React\Socket\Server;
use React\Socket\SecureServer;
use React\Socket\SocketServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:start-server')
]
class StartServerCommand extends Command
{
    const string WEBSOCKET_CERT = '/etc/ssl/certs/ssl-cert-snakeoil.pem';
    const string WEBSOCKET_KEY = '/etc/ssl/private/ssl-cert-snakeoil.key';
    private bool $secure = false;
    private string $host = 'localhost';
    private int $port = 0;
    private string $scheme = 'ws';
    private string $cert = '';
    private string $key = '';
    private bool $allowSelfSigned = false;
    private bool $verifyPeer = true;

    private InputInterface $input;
    private OutputInterface $output;

    public function __construct(
        private readonly ParameterBagInterface $parameters,
        ?string $name = 'app:start-server',
    ){
        parent::__construct($name);

        $this->addOption(
            'url',
            'u',
            InputOption::VALUE_OPTIONAL,
            'Url to run the server on',
            $this->parameters->get('websocket_url')
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->setup($input, $output);
        $this->startWebsocketServer();
    }

    private function setup(InputInterface $input, OutputInterface $output) : void
    {
        $this->output = $output;
        $this->input = $input;
        $this->cert =
            $this->parameters->has('websocket_cert') && $this->parameters->get('websocket_cert') !== ''
                ? $this->parameters->get('websocket_cert') ?? self::WEBSOCKET_CERT
                : self::WEBSOCKET_CERT;
        $this->key =
            $this->parameters->has('websocket_key') && $this->parameters->get('websocket_key') !== ''
                ? $this->parameters->get('websocket_key') ?? self::WEBSOCKET_KEY
                : self::WEBSOCKET_KEY;

        $this->allowSelfSigned = $this->cert === self::WEBSOCKET_CERT && $this->key === self::WEBSOCKET_KEY;
        $this->verifyPeer = !$this->allowSelfSigned;
        $this->processUrl($input->getOption('url'));
    }
    private function startWebsocketServer() : void
    {
        if(!$this->secure) {
            // Initialize non-SSL WebSocket server - this one works!
            $webServer = IoServer::factory(
                new HttpServer(
                    new WsServer(
                        new Chat()
                    )
                ),
                $this->port
            );
        } else {
            $loop = Loop::get();
            // Apparently 0.0.0.0 is the same as localhost, but it allows for external connections
            $this->host = '0.0.0.0';

            $websocket = new SocketServer($this->host . ':' . $this->port, [], $loop);
            # deprecated
            # $webSock = new Server('0.0.0.0:' . $port, $loop);

            $secureWebSock = new SecureServer($websocket, $loop, [
                'local_cert'        => $this->cert, // Path to your cert
                'local_pk'          => $this->key, // Path to your private key
                'allow_self_signed' => $this->allowSelfSigned, // Allow self signed certs (not recommended for production)
                'verify_peer'       => $this->verifyPeer // This is for client verification
            ]);

            // Set up the WebSocket server
            $webServer = new IoServer(
                new HttpServer(
                    new WsServer(
                        new Chat() // Your Chat class that implements MessageComponentInterface
                    )
                ),
                // we should be able to toggle secure with this method too? - does not seem to work
                $this->secure ? $secureWebSock : $websocket,
                $loop
            );

            /////////////////////////////////////
            /// code by copilot
            /// not tested
            /////////////////////////////////////

            // Initialize SSL WebSocket server - this one doesn't work!
//            $webServer = IoServer::factory(
//                new HttpServer(
//                    new WsServer(
//                        new Chat()
//                    )
//                ),
//                new SecureServer(
//                    new SocketServer($this->scheme . '://' . $this->host . ':' . $this->port),
//                    $this->output,
//                    [
//                        'local_cert' => $this->cert,
//                        'local_pk' => $this->key,
//                        'allow_self_signed' => $this->allowSelfSigned,
//                        'verify_peer' => $this->verifyPeer
//                    ]
//                )
//            );
        }


        $this->output->writeln('Starting server on ' . $this->scheme . '://' . $this->host . ':' . $this->port);
        $webServer->run();
    }
    private function processUrl(string $url): void
    {
        $parts = $this->validateUrl($url);
        $this->scheme = $parts['scheme'];
        $this->secure = $this->scheme === 'wss';
        $this->port = $parts['port'];
        $this->host = $parts['host'];

        $secureText = $this->secure ? '<info> [Secure ] </info>' : '<comment> [ INSECURE ] </comment>';
        if($this->input->getOption('verbose')) {
            $this->output->writeln('Full URL ' . $this->scheme . '://' . $this->host . ':' . $this->port . $secureText);

            if($this->secure) {
                $this->output->writeln('Allowing self signed certificates: ' . ($this->allowSelfSigned ? 'Yes' : 'No'));
                $this->output->writeln('Verifying peer: ' . ($this->verifyPeer ? 'Yes' : 'No'));
                $this->output->writeln('Used cert: ' . $this->cert);
                $this->output->writeln('Used key: ' . $this->key);
            }
        }
    }
    private function validateUrl(string $url): array
    {
        // Were expecting a format like ws://host:port or wss://host:port, so we need to parse the URL
        $urlParts = parse_url($url);
        // $port should be int between 1 and 65535
        if (!is_int($urlParts['port']) || $urlParts['port'] < 1 || $urlParts['port'] > 65535) {
            throw new \InvalidArgumentException('Port must be an integer between 1 and 65535');
        }

        // $scheme should be either 'ws' or 'wss'
        if (!in_array($urlParts['scheme'], ['ws', 'wss'])) {
            throw new \InvalidArgumentException('Scheme must be either ws or wss');
        }

        // $host should be a valid domain name or IP address
        if (!filter_var($urlParts['host'], FILTER_VALIDATE_IP) && !filter_var($urlParts['host'], FILTER_VALIDATE_DOMAIN)) {
            throw new \InvalidArgumentException('Host must be a valid domain name or IP address');
        }

        return $urlParts;
    }
}
