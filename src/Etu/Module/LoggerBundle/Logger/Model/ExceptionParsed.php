<?php

namespace Etu\Module\LoggerBundle\Logger\Model;

use Exception;

/**
 * Exception parser
 *
 * Understand an exception and simplify its display.
 */
class ExceptionParsed
{
	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @var string
	 */
	protected $class;

	/**
	 * @var int|mixed
	 */
	protected $code;

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @var int
	 */
	protected $line;

	/**
	 * @var array
	 */
	protected $linesAround;

	/**
	 * @var array
	 */
	protected $trace;

	/**
	 * @var string
	 */
	protected $stringTrace;

	/**
	 * @var array
	 */
	protected $stack;

	/**
	 * @var Exception
	 */
	protected $previous;

	/**
	 * @var Exception
	 */
	protected $exception;

	/**
	 * Constructor
	 */
	public function __construct(Exception $exception)
	{
		$this->message = $exception->getMessage();
		$this->code = $exception->getCode();
		$this->file = $exception->getFile();
		$this->class = get_class($exception);
		$this->line = $exception->getLine();
		$this->trace = $exception->getTrace();
		$this->stringTrace = $exception->getTraceAsString();
		$this->previous = $exception->getPrevious();
		$this->exception = $exception;
		$this->linesAround = array();

		$this->generateLinesAround();
		$this->generateStack();
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function set($key, $value)
	{
		$this->$key = $value;
	}

	/**
	 * @return array
	 */
	public function export()
	{
		$exception = get_object_vars($this);
		unset($exception['trace']);
		unset($exception['previous']);
		unset($exception['exception']);

		foreach ((array) $exception['stack'] as $key => $e) {
			$exception['stack'][$key] = $e->export();
		}

		return $exception;
	}

	/**
	 * @param array $exceptionArray
	 * @return ExceptionParsed
	 */
	public static function import(array $exceptionArray)
	{
		$exception = new self(new Exception);
		$exception->set('message', $exceptionArray['message']);
		$exception->set('class', $exceptionArray['class']);
		$exception->set('code', $exceptionArray['code']);
		$exception->set('file', $exceptionArray['file']);
		$exception->set('line', $exceptionArray['line']);
		$exception->set('linesAround', $exceptionArray['linesAround']);
		$exception->set('stringTrace', $exceptionArray['stringTrace']);

		$stack = array();

		foreach ((array) $exceptionArray['stack'] as $childArray) {
			$stack[] = self::import($childArray);
		}

		$exception->set('stack', $stack);

		return $exception;
	}

	/**
	 * Find lines around the exception call
	 */
	protected function generateLinesAround()
	{
		$handler = fopen($this->file, 'r');

		if ($handler) {
			for ($i = 1; ($buffer = fgets($handler, 4096)) !== false; $i++) {
				if ($i >= $this->line - 3 && $i <= $this->line + 3) {
					$this->linesAround[$i] = trim($buffer, "\n\r");
				} elseif ($i > $this->line + 3) {
					break;
				}
			}

			fclose($handler);
		}
	}

	/**
	 * Generate the stack of previous exceptions
	 */
	protected function generateStack()
	{
		if (! $this->exception->getPrevious() instanceof Exception) {
			return;
		}

		$currentException = $this->exception->getPrevious();

		while ($currentException->getPrevious() instanceof Exception) {
			$this->stack[] = new self($currentException);
			$currentException = $currentException->getPrevious();
		}

		$this->stack[] = new self($currentException);
	}

	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * @return int|mixed
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @return \Exception
	 */
	public function getException()
	{
		return $this->exception;
	}

	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * @return int
	 */
	public function getLine()
	{
		return $this->line;
	}

	/**
	 * @return array
	 */
	public function getLinesAround()
	{
		return $this->linesAround;
	}

	/**
	 * @return array
	 */
	public function getLinesAroundBiggestNumberLineSize()
	{
		$max = -1;

		foreach ($this->linesAround as $number => $line) {
			if (strlen((string) $number) > $max) {
				$max = strlen((string) $number);
			}
		}

		return $max;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @return boolean
	 */
	public function hasMessage()
	{
		return ! empty($this->message);
	}

	/**
	 * @return \Exception
	 */
	public function getPrevious()
	{
		return $this->previous;
	}

	/**
	 * @return array
	 */
	public function getStack()
	{
		return $this->stack;
	}

	/**
	 * @return string
	 */
	public function getStringTrace()
	{
		return $this->stringTrace;
	}

	/**
	 * @return string
	 */
	public function getStringTraceLines()
	{
		return explode("\n", $this->getStringTrace());
	}

	/**
	 * @return array
	 */
	public function getTrace()
	{
		return $this->trace;
	}
}