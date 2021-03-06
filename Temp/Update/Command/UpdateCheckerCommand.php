<?php

/*
 * This file is part of the Update Checker.
 *
 * (c) 2014 Stephan Wentz
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Temp\Update\Command;

use Temp\Update\UpdateChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Temp\Update\Formatters\JsonFormatter;
use Temp\Update\Formatters\TextFormatter;

class UpdateCheckerCommand extends Command
{
    private $checker;

    public function __construct(UpdateChecker $checker)
    {
        $this->checker = $checker;

        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('check')
            ->setDefinition(array(
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'The output format', 'text'),
                new InputArgument('lock', InputArgument::OPTIONAL, 'The path to the composer.lock file', 'composer.lock')
            ))
            ->setDescription('Show updates in your project dependencies')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command checks a <info>composer.lock</info>
file for updates in the project dependencies:

<info>php %command.full_name% /path/to/composer.lock</info>
EOF
            );
    }

    /**
     * @see Command
     * @see SecurityChecker
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $data = $this->checker->check($input->getArgument('lock'));
        } catch (\Exception $e) {
            $output->writeln($this->getHelperSet()->get('formatter')->formatBlock($e->getMessage(), 'error', true));

            return 1;
        }

        switch ($input->getOption('format')) {
            case 'json':
                $formatter = new JsonFormatter();
                break;
            case 'text':
            default:
                $formatter = new TextFormatter($this->getHelperSet()->get('table'));
        }
        $formatter->displayResults($output, $input->getArgument('lock'), $data);


        return 0;
    }
}
