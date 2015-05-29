<?php
/*************************************************************************************/
/* This file is part of the Thelia package.                                          */
/*                                                                                   */
/* Copyright (c) OpenStudio                                                          */
/* email : dev@thelia.net                                                            */
/* web : http://www.thelia.net                                                       */
/*                                                                                   */
/* For the full copyright and license information, please view the LICENSE.txt       */
/* file that was distributed with this source code.                                  */
/*************************************************************************************/

namespace ConfigDoc\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Yaml;
use Thelia\Command\ContainerAwareCommand;
use Thelia\Command\Output\TheliaConsoleOutput;
use Thelia\Core\Thelia;
use Thelia\Model\ConfigI18nQuery;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Map\ConfigI18nTableMap;
use Thelia\Model\Map\ConfigTableMap;

/**
 * Class ConfigDocExport
 * @package ConfigDoc\Command
 * @author Benjamin Perche <benjamin@thelia.net>
 */
class ConfigDocExport extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("thelia:config:export")
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_OPTIONAL,
                'export format wanted : json, xml, yml or array',
                'json'
            )
            ->addOption(
                'lang',
                'l',
                InputOption::VALUE_OPTIONAL,
                'The lang to export the configuration.',
                'en_US'
            )
            ->addOption(
                'output-file',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Write the output in this file'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param \Thelia\Command\Output\TheliaConsoleOutput $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! Thelia::isInstalled()) {
            $this->displayError($output, [
                "",
                "You need to install Thelia before exporting the config variables.",
                "",
            ]);

            return 1;
        }

        $collectedData = $this->collectConfigData($input->getOption("lang"));

        switch ($format = $input->getOption("format")) {
            case "json":
                $encodedData = json_encode($collectedData);
                break;

            case "yml":
                $encodedData = Yaml::dump($collectedData);
                break;

            case "array":
                $encodedData = var_export($collectedData);
                break;

            case "xml":
                $serializer = new Serializer([], [new XmlEncoder('hooks')]);
                $encodedData = $serializer->encode($collectedData, 'xml');
                break;

            default:
                throw new \RuntimeException(sprintf('format %s not supported', $format));
        }

        if (null !== $file = $input->getOption("output-file")) {
            $fileHandler = @fopen($file, "w");

            if (false === $fileHandler) {
                $this->displayError($output, [
                    "",
                    "Unable to write in the file '$file'",
                    "",
                ]);

                return 2;
            }

            fprintf($fileHandler, "%s", $encodedData);
            fclose($fileHandler);
        } else {
            $output->write($encodedData, false, OutputInterface::OUTPUT_RAW);
        }

        return 0;
    }

    /**
     * @return array
     */
    protected function collectConfigData($locale)
    {
        return ConfigQuery::create()
            ->useI18nQuery()
                ->filterByLocale($locale)
                ->addAsColumn("title", ConfigI18nTableMap::TITLE)
                ->addAsColumn("description", ConfigI18nTableMap::DESCRIPTION)
            ->endUse()
            ->addAsColumn("name", ConfigTableMap::NAME)
            ->select(["name", "title", "description"])
            ->find()
            ->toArray()
        ;
    }

    protected function displayError(TheliaConsoleOutput $output, array $message)
    {
        $output->renderBlock($message, "bg=red;fg=white");
    }
}
