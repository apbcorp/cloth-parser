<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ZeanParserCommand
 * @package App\Command
 */
class ZeanParserCommand extends Command
{
    private const CATALOG_LINK = 'https://www.zean.ua/vse-tovary/#/sort=p.sort_order/order=ASC/limit=10/page={page}';

    protected static $defaultName = 'parser:parse:zean';

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setDescription('Parser for "zean.ua"')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}