<?php

namespace App\Command;

use App\Library\Benzina\Benzina;
use App\Library\Benzina\Pump\PumpInterface;
use App\Library\Benzina\Stream\PdoStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:benzina:pump',
    description: 'Pump records in a database into the v4 database.',
)]
class BenzinaPumpCommand extends Command
{
    public function __construct(
        private Benzina $benzina,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('table', InputArgument::REQUIRED);

        $this->addOption(
            'database',
            null,
            InputOption::VALUE_OPTIONAL,
            'The address of the database to read from',
            'mysql://goteo:goteo@mariadb:3306/benzina'
        );

        $this->addOption(
            'batch-size',
            null,
            InputOption::VALUE_OPTIONAL,
            'The number of rows to process at once',
            99
        );

        $this->addUsage('app:benzina:pump --no-debug user');
        $this->setHelp(
            <<<'EOF'
The <info>%command.name%</info> processes the data in the database table and supplies it to the supporting pumps:

    <info>%command.full_name%</info>

You can avoid possible memory leaks caused by the Symfony profiler with the <info>no-debug</info> flag:

    <info>%command.full_name% --no-debug</info>
EOF
        );
    }

    /**
     * @param ConsoleOutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = $input->getOption('batch-size');

        $stream = new PdoStream(
            $input->getOption('database'),
            $input->getArgument('table'),
            $batchSize
        );
        $streamSize = $stream->size();
        $streamBatches = $streamSize / $batchSize;
        $streamSection = new SymfonyStyle($input, $output->section());

        if ($streamSize < 1) {
            $streamSection->writeln('No data found at the given source. Skipping execution.');

            return Command::SUCCESS;
        }

        $streamSection->writeln(sprintf('Streaming %d records (%d batches).', $streamSize, $streamBatches));

        $pumps = $this->benzina->getPumpsFor($stream);
        $pumpsCount = \count($pumps);
        $pumpsSection = new SymfonyStyle($input, $output->section());

        if ($pumpsCount < 1) {
            $pumpsSection->writeln('No pumps support the streamed sample. Skipping execution.');

            return Command::SUCCESS;
        }

        $pumpsSection->writeln(sprintf('Streaming to %d pumps.', $pumpsCount));
        $pumpsSection->listing(\array_map(function (PumpInterface $pump) {
            return $pump::class;
        }, $pumps));

        $etaSection = $output->section();
        $etaSection->writeln('ETA');

        $streamedBatchesSection = $output->section();
        $streamedBatchesSection->writeln("\tStreamed batches:");
        $streamedBatches = new ProgressBar($streamedBatchesSection);
        $streamedBatches->start($streamBatches);

        $streamedRecordsSection = $output->section();
        $streamedRecordsSection->writeln("\tStreamed records:");
        $streamedRecords = new ProgressBar($streamedRecordsSection);
        $streamedRecords->start($streamSize);

        $memUsageSection = $output->section();
        $memUsageSection->writeln("\tMemory usage:");
        $memUsage = new ProgressBar($memUsageSection);
        $memUsage->start($this->getMemoryLimit());

        while (!$stream->eof()) {
            \memory_reset_peak_usage();

            $batch = $stream->read();
            $batchStartTime = \microtime(true);
            $streamedBatches->advance();

            foreach ($pumps as $pump) {
                $pump->pump($batch);

                $memUsage->setProgress(\memory_get_peak_usage(false));
            }

            $streamed = $stream->tell();
            $streamedRecords->setProgress($streamed);

            $batchEndTime = \microtime(true);
            $batchTime = $batchEndTime - $batchStartTime;

            $eta = round((($streamSize - $streamed) / count($batch)) * $batchTime);
            $etaSection->overwrite(sprintf('ETA %02d:%02d:%02d', $eta / 3600, floor($eta / 60) % 60, $eta % 60));
        }

        $endSection = new SymfonyStyle($input, $output->section());
        $endSection->success('Data processed successfully!');

        return Command::SUCCESS;
    }

    private function getMemoryLimit(): int
    {
        $limit = \ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $limit, $matches)) {
            if ($matches[2] == 'M') {
                $limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
            } else if ($matches[2] == 'K') {
                $limit = $matches[1] * 1024; // nnnK -> nnn KB
            }
        }

        return $limit;
    }
}
