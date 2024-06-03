<?php

namespace App\Command;

use App\Library\Economy\Payment\GatewayLocator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:gateways:setup',
    description: 'Post install setup for Gateways.',
)]
class GatewaysSetupCommand extends Command
{
    private const SUCCESS_MESSAGE = "Gateways setup completed successfully!";
    private const FAILURE_MESSAGE = "Could not setup Gateways. Please review the error.";

    public function __construct(
        private GatewayLocator $gatewayLocator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->gatewayLocator->compileGatewayNames();

            $io->success(self::SUCCESS_MESSAGE);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(self::FAILURE_MESSAGE);

            throw $e;
        }
    }
}
