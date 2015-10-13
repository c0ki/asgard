<?php

namespace Recette\ImportBundle\Component\Csv;

use Recette\ImportBundle\Component\Xlsx\Exception\ParseException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;

class Parser
{

    /*
     * @var
     * $container
     * \Symfony\Component\DependencyInjection\Container
     */
    protected $container = null;

    const TemporaryPath = 'recette/csvparser/tmp/';

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function parse($inputFile, $headLine = 0, $limitRow = null)
    {
        // Check inputFile
        if (empty($inputFile)) {
            throw new ParseException('The input file is empty');
        };

        // Create a File instance
        if (!$inputFile instanceof File) {
            $inputFile = new File($inputFile, true);
        }

        // Temporary directory
        $temporaryDir = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . self::TemporaryPath;

        $csv = array_map('str_getcsv', file('data.csv'));

        $header = array();
        $content = array();
        $maxCols = 0;
        $nbRows = 1;
        foreach ($xmlSheet->xpath('//default:sheetData/default:row') as $row) {
            if ($limitRow && $nbRows > $limitRow) {
                break;
            }
            if ($headLine && $nbRows < $headLine) {
                continue;
            }
            $line = array();
            $row->registerXPathNamespace('default', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($row->xpath('default:c') as $idCol => $cell) {
                $valueId = (String)$cell->v;
                $value = null;
                switch ((String)$cell['t']) {
                    case 's':
                        $values = $xmlValues->xpath("//default:si[position()={$valueId}+1]//default:t");
                        $value = (String)$values[0];
                        break;
                    case 'n':
                        $dateTime = new \DateTime('1900-01-00 00:00:00');
                        $date = floor($valueId);
                        $time = round(60 * 60 * 24 * ($valueId - $date));
                        if ($date > 60) {
                            $date--;
                        }
                        $dateTime->add(new \DateInterval("P{$date}D"));
                        $dateTime->add(new \DateInterval("PT{$time}S"));
                        $value = $dateTime->format('Y-m-d H:i:s');
                        break;
                }
                if (!empty($header)) {
                    $line[$header[$idCol]] = $value;
                }
                else {
                    $line[] = $value;
                }
                if (!is_null($value) && $idCol > $maxCols) {
                    $maxCols = $idCol;
                }

            }
            $line_min = array_filter($line);
            if (empty($line_min)) {
                continue;
            }
            if ($nbRows == $headLine) {
                $header = $line;
                $nbRows++;
            }
            else {
                $content[] = $line;
            }
            $nbRows++;
        }
        array_walk($content, function (&$line) use ($maxCols) {
            $line = array_slice($line, 0, $maxCols + 1);
        });

        // Cleanup
        if (!$this->container->getParameter('kernel.debug')) {
            $fs = new Filesystem();
            $fs->remove($temporaryDir);
        }

        return $content;
    }

}


?>