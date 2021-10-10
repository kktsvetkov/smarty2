<?php

namespace Smarty2\Exception;

use UnexpectedValueException;

use function sprintf;

class SyntaxException extends UnexpectedValueException
{
        protected string $templateMessage;
        protected string $templateName;
        protected int $templateLine;

        function __construct(string $message, string $templateName, int $templateLine)
        {
                $this->templateMessage = $message;
                $this->templateName = $templateName;
                $this->templateLine = $templateLine;

                parent::__construct( $this->getTemplateError() );
        }

        function getTemplateError() : string
        {
                return sprintf(
                        '%s [in %s line %d]',
                        $this->templateMessage,
                        $this->templateName,
                        $this->templateLine
                );
        }

        function getTemplateMessage() : string
        {
                return $this->templateMessage;
        }

        function getTemplateName() : string
        {
                return $this->templateName;
        }

        function getTemplteLine() : int
        {
                return $this->templateLine;
        }
}
