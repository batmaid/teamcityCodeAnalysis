<?php

use Spatie\ArrayToXml\ArrayToXml;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TeamcityCodeAnalysisTransformCommand extends Command
{

    /**
     * @throws LogicException
     */
    public function __construct(
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('teamcity:TransformCodeAnalysisOutput')
            ->setDescription('Transform the XML output of phpStorm CA into PMD format readable by Teamcity.')
            ->addArgument(
                'inputDir',
                InputArgument::REQUIRED,
                'Directory containing CA output XML files.'
            )->addArgument(
                'outputFile',
                InputArgument::REQUIRED,
                'Where to place the output PMD file.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $matchingFiles = $this->getInputFiles($input);
        $outputFile = $input->getArgument("outputFile");
        $problemsPerFile = $this->parseInputFiles($matchingFiles);

        $pmdData = $this->transformToPmdFormat($problemsPerFile);

        $xmlOutput = ArrayToXml::convert($pmdData, "pmd");
        file_put_contents($outputFile, $xmlOutput);

        $output->writeln("##teamcity[importData type='pmd' path='$outputFile']");
    }

    protected function parseInputFiles(array $matchingFiles): array
    {
        $problemsPerFile = [];

        foreach ($matchingFiles as $file) {

            $xml = new \SimpleXMLElement(file_get_contents($file));

            /** @noinspection PhpUndefinedFieldInspection */
            foreach ($xml->problem as $problem) {
                /** @noinspection PhpUndefinedFieldInspection */
                $affectedFile = trim((string)$problem->file);
                $affectedFile = $this->normalizeFilename($affectedFile);
                /** @noinspection PhpUndefinedFieldInspection */
                $affectedLine = trim((string)$problem->line);
                /** @noinspection PhpUndefinedFieldInspection */
                $problemClass = trim((string)$problem->problem_class);
                /** @noinspection PhpUndefinedFieldInspection */
                $description = trim((string)$problem->description);
                /** @noinspection PhpUndefinedFieldInspection */
                $severity = trim((string)$problem->problem_class["severity"]);

                $problemInstance = [
                    "line" => $affectedLine,
                    "class" => $problemClass,
                    "description" => $description,
                    "severity" => $severity,
                ];
                $fileProblems = array_key_exists($affectedFile, $problemsPerFile) ? $problemsPerFile[$affectedFile] : [];
                $fileProblems[] = $problemInstance;

                $problemsPerFile[$affectedFile] = $fileProblems;
            }
        }

        return $problemsPerFile;
    }

    private function transformToPmdFormat(array $problemsPerFile): array
    {
        $files = [];
        foreach ($problemsPerFile as $file => $problemInstances) {

            $violations = [];
            foreach ($problemInstances as $problemInstance) {
                $violations[] = [
                    "_attributes" => [
                        "line" => $problemInstance["line"],
                        "beginline" => $problemInstance["line"],
                        "endline" => $problemInstance["line"],
                        "rule" => $problemInstance["class"],
                        "priority" => $this->mapSeverityToPriority($problemInstance["severity"]),
                    ],
                    "_value" => $problemInstance["description"],
                ];
            }
            $files[] = [
                "_attributes" => ["name" => $file],
                "violation" => $violations
            ];
        }

        $pmd = [
            "_attributes" => ["timestamp" => (new \DateTime())->format("r")],
            "file" => $files,
        ];

        return $pmd;
    }

    private function getInputFiles(InputInterface $input): array
    {
        $inputDir = $input->getArgument("inputDir");
        $files = scandir($inputDir);

        $matchingFiles = array_filter($files, function ($file) {
            return preg_match('/.xml/', $file);
        });

        return array_map(function ($file) use ($inputDir) {
            return $inputDir . "/" . $file;
        }, $matchingFiles);
    }

    private function normalizeFilename($affectedFile)
    {
        return str_replace('file://$PROJECT_DIR$/', "", $affectedFile);
    }

    private function mapSeverityToPriority($severity)
    {
        switch ($severity) {
            case "ERROR":
                return 1;
            case "WARNING":
                return 2;
            case "WEAK WARNING":
                return 3;
            default:
                throw new \Exception("unknown severity $severity");
        }
    }
}
