<?php

namespace App\Command;

use App\Library\Benzina\Benzina;
use App\Library\Benzina\Stream\PdoStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:benzina:pump',
    description: 'Pump records in a database into the v4 database.',
)]
class BenzinaPumpCommand extends Command
{
    public function __construct(
        private Benzina $benzina
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('table', InputArgument::REQUIRED);

        $this->addOption(
            'batch-size',
            null,
            InputOption::VALUE_OPTIONAL,
            'The number of rows to process at once',
            99
        );

        $this->addOption(
            'no-progress',
            null,
            InputOption::VALUE_NONE,
            'For progressive pumps, disables the override of already pumped progress'
        );

        $this->addOption(
            'database',
            null,
            InputOption::VALUE_OPTIONAL,
            'The address of the database to read from',
            'mysql://goteo:goteo@mariadb:3306/benzina'
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $stream = new PdoStream(
            $input->getOption('database'),
            $input->getArgument('table'),
            $input->getOption('batch-size')
        );
        $streamSize = $stream->size();

        if ($streamSize < 1) {
            $io->writeln('No data found at the given source. Skipping execution.');

            return Command::SUCCESS;
        }

        $pumps = $this->benzina->getPumps($stream);
        $pumpsCount = count($pumps);

        if ($pumpsCount < 1) {
            $io->writeln('No pumps available for the data. Skipping execution.');

            return Command::SUCCESS;
        }

        $io->writeln(sprintf('Found %d pumps.', $pumpsCount));
        $io->listing(\array_map(function ($pump) {
            return $pump::class;
        }, $pumps));

        $io->writeln(sprintf('Processing %d records.', $streamSize));
        $progress = $io->createProgressBar();
        $progress->start($streamSize);

        while (!$stream->eof()) {
            $data = $stream->read();

            foreach ($pumps as $pump) {
                $pump->configure([
                    'progress' => !$input->getOption('no-progress'),
                ]);

                $pump->process($data);
            }

            $progress->setProgress($stream->tell());
        }

        $stream->close();
        $progress->finish();

        $io->writeln("\n");
        $io->success('Data processed successfully!');

        return Command::SUCCESS;
    }
}
