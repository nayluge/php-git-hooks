<?php

namespace PhpGitHooks\Module\PhpCsFixer\Tests\Behaviour;

use PhpGitHooks\Module\Configuration\Tests\Stub\ConfigurationDataResponseStub;
use PhpGitHooks\Module\Git\Contract\Response\BadJobLogoResponse;
use PhpGitHooks\Module\Git\Service\PreCommitOutputWriter;
use PhpGitHooks\Module\Git\Tests\Stub\FilesCommittedStub;
use PhpGitHooks\Module\PhpCsFixer\Contract\Command\PhpCsFixerToolCommand;
use PhpGitHooks\Module\PhpCsFixer\Contract\CommandHandler\PhpCsFixerToolCommandHandler;
use PhpGitHooks\Module\PhpCsFixer\Contract\Exception\PhpCsFixerViolationsException;
use PhpGitHooks\Module\PhpCsFixer\Service\PhpCsFixerTool;
use PhpGitHooks\Module\PhpCsFixer\Tests\Infrastructure\PhpCsFixerUnitTestCase;

class PhpCsFixerToolCommandHandlerTest extends PhpCsFixerUnitTestCase
{
    /**
     * @var PhpCsFixerToolCommandHandler
     */
    private $phpCsFixerToolCommandHandler;

    protected function setUp()
    {
        $this->phpCsFixerToolCommandHandler = new PhpCsFixerToolCommandHandler(
            new PhpCsFixerTool(
                $this->getOutputInterface(),
                $this->getPhpCsFixerToolProcessor()
            )
        );
    }

    /**
     * @test
     */
    public function itShouldThrowsException()
    {
        $this->expectException(PhpCsFixerViolationsException::class);

        $configurationData = ConfigurationDataResponseStub::createAllEnabled();
        $phpFiles = FilesCommittedStub::createOnlyPhpFiles();

        $outputMessage = new PreCommitOutputWriter('Checking PSR0 code style with PHP-CS-FIXER');
        $this->shouldWriteOutput($outputMessage->getMessage());

        $errors = null;
        foreach ($phpFiles as $file) {
            $errorText = 'ERROR';
            $this->shouldProcessPhpCsFixerTool($file, 'PSR0', $errorText);
            $errors .= $errorText;
        }

        $this->shouldWriteLnOutput($outputMessage->getFailMessage());
        $this->shouldWriteLnOutput($outputMessage->setError($errors));
        $this->shouldWriteLnOutput(BadJobLogoResponse::paint($configurationData->getPreCommit()->getErrorMessage()));

        $this->phpCsFixerToolCommandHandler->handle(
            new PhpCsFixerToolCommand(
                $phpFiles,
                $configurationData->getPreCommit()->getPhpCsFixer()->isPhpCsFixerPsr0(),
                $configurationData->getPreCommit()->getPhpCsFixer()->isPhpCsFixerPsr1(),
                $configurationData->getPreCommit()->getPhpCsFixer()->isPhpCsFixerPsr2(),
                $configurationData->getPreCommit()->getPhpCsFixer()->isPhpCsFixerSymfony(),
                $configurationData->getPreCommit()->getErrorMessage()
            )
        );
    }

    /**
     * @test
     */
    public function itShouldWorksFine()
    {
        $configurationData = ConfigurationDataResponseStub::createAllEnabled();
        $phpFiles = FilesCommittedStub::createOnlyPhpFiles();

        $outputMessagePsr0 = new PreCommitOutputWriter('Checking PSR0 code style with PHP-CS-FIXER');
        $this->shouldWriteOutput($outputMessagePsr0->getMessage());

        foreach ($phpFiles as $file) {
            $this->shouldProcessPhpCsFixerTool($file, 'PSR0', null);
        }

        $this->shouldWriteLnOutput($outputMessagePsr0->getSuccessfulMessage());

        $outputMessagePsr1 = new PreCommitOutputWriter('Checking PSR1 code style with PHP-CS-FIXER');
        $this->shouldWriteOutput($outputMessagePsr1->getMessage());

        foreach ($phpFiles as $file) {
            $this->shouldProcessPhpCsFixerTool($file, 'PSR1', null);
        }

        $this->shouldWriteLnOutput($outputMessagePsr1->getSuccessfulMessage());

        $outputMessagePsr2 = new PreCommitOutputWriter('Checking PSR2 code style with PHP-CS-FIXER');
        $this->shouldWriteOutput($outputMessagePsr2->getMessage());

        foreach ($phpFiles as $file) {
            $this->shouldProcessPhpCsFixerTool($file, 'PSR2', null);
        }

        $this->shouldWriteLnOutput($outputMessagePsr2->getSuccessfulMessage());

        $outputMessageSymfony = new PreCommitOutputWriter('Checking SYMFONY code style with PHP-CS-FIXER');
        $this->shouldWriteOutput($outputMessageSymfony->getMessage());

        foreach ($phpFiles as $file) {
            $this->shouldProcessPhpCsFixerTool($file, 'SYMFONY', null);
        }

        $this->shouldWriteLnOutput($outputMessageSymfony->getSuccessfulMessage());

        $this->phpCsFixerToolCommandHandler->handle(
            new PhpCsFixerToolCommand(
                $phpFiles,
                $configurationData->getPreCommit()->getPhpCsFixer()->isPhpCsFixerPsr0(),
                $configurationData->getPreCommit()->getPhpCsFixer()->isPhpCsFixerPsr1(),
                $configurationData->getPreCommit()->getPhpCsFixer()->isPhpCsFixerPsr2(),
                $configurationData->getPreCommit()->getPhpCsFixer()->isPhpCsFixerSymfony(),
                $configurationData->getPreCommit()->getErrorMessage()
            )
        );
    }
}