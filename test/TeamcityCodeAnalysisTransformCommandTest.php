<?php

namespace test;

use PHPUnit\Framework\TestCase;
use src\TeamcityCodeAnalysisTransformCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class TeamcityCodeAnalysisTransformCommandTest extends TestCase
{
    public $outputFile;
    /** @var Command */
    private $command;
    private $commandInput;
    /** @var  BufferedOutput */
    private $commandOutput;
    /** @var  Application */
    private $application;

    public function setUp()
    {
        parent::setUp();

        $this->application = new Application();
        $this->command = $this->registerCommandInstanceForTesting(new TeamcityCodeAnalysisTransformCommand());
        $this->outputFile = __DIR__ . "/Samples/" . uniqid() . ".xml";
        $this->commandInput = new ArrayInput([
            'command' => $this->command->getName(),
            "inputDir" => __DIR__ . "/Samples",
            "outputFile" => $this->outputFile,
        ]);
        $this->commandOutput = new BufferedOutput();
        $this->command->run($this->commandInput, $this->commandOutput);
    }

    protected function tearDown()
    {
        unlink($this->outputFile);

        parent::tearDown();
    }

    public function testExecute_combinesProblemReportsIntoSinglePmdFile()
    {
        $outputXml = new \SimpleXMLElement(file_get_contents($this->outputFile));
        $file = $this->findNode($outputXml,'src/FileWithMultipleViolationTypes.php');

        self::assertNotNull($file);
        self::assertEquals(2, $file->violation->count());
        foreach ($file->violation as $violation) {

            self::assertNotEmpty($violation["line"]);
            self::assertNotEmpty($violation["rule"]);
            self::assertNotEmpty($violation["priority"]);
            self::assertStringStartsWith("Undefined class", (string)$violation);
        }
    }
    public function testExecute_distinguishesErrorsFromWarnings()
    {
        $outputXml = new \SimpleXMLElement(file_get_contents($this->outputFile));

        $fileWithWarning = $this->findNode($outputXml,'src/FileWithWarning.php');
        $fileWithWeakWarning = $this->findNode($outputXml,'src/FileWithWeakWarning.php');
        $fileWithError = $this->findNode($outputXml,'src/FileWithError.php');

        self::assertEquals(1, current($fileWithError->violation)["priority"]);
        self::assertEquals(2, current($fileWithWarning->violation)["priority"]);
        self::assertEquals(3, current($fileWithWeakWarning->violation)["priority"]);
    }

    public function testExecute_printsTeamcityInstruction()
    {
        $output = $this->outputFile;
        self::assertEquals(
            "##teamcity[importData type='pmd' path='$output']",
            trim($this->commandOutput->fetch())
        );
    }

    private function findNode($xmlOutput, $expectedName)
    {
        foreach ($xmlOutput as $file) {
            if ($file["name"] == $expectedName) {
                return $file;
            }
        }

        return null;
    }

    private function registerCommandInstanceForTesting(Command $command): Command
    {
        $name = $command->getName();
        $this->application->all(); //makes sure the command does not get overridden by lazy init code
        $this->application->add($command);
        assert($this->application->find($name) === $command, "command registration failed");

        return $command;
    }
}
