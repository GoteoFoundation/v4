<?php

namespace App\Command;

use App\Library\Benzina\Benzina;
use App\Library\Benzina\Pump\PumpInterface;
use App\Library\Benzina\Stream\PdoStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:benzina:pump',
    description: 'Pump records in a database into the v4 database.',
)]
class BenzinaPumpCommand extends Command
{
    private const SUCCESS_MESSAGE = "Gateways setup completed successfully!";
    private const FAILURE_MESSAGE = "Could not setup Gateways. Please review the error.";

    public function __construct(
        private Benzina $benzina
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('database', InputArgument::REQUIRED);
        $this->addArgument('table', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $stream = new PdoStream($input->getArgument('database'), $input->getArgument('table'));
        $pumps = $this->benzina->getPumps($stream);

        $progress = $io->createProgressBar();
        $progress->start($stream->size());

        while (!$stream->eof()) {
            $data = $stream->read();

            foreach ($pumps as $pump) {
                $pump->process($data);
            }

            $progress->setProgress($stream->tell());
        }

        $stream->close();
        $progress->finish();

        return Command::SUCCESS;
    }
}
