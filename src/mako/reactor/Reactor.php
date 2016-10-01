<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor;

use Closure;

use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\cli\output\helpers\Table;
use mako\reactor\Dispatcher;
use mako\reactor\exceptions\InvalidArgumentException;
use mako\reactor\exceptions\InvalidOptionException;
use mako\reactor\exceptions\MissingArgumentException;
use mako\reactor\exceptions\MissingOptionException;
use mako\reactor\traits\SuggestionTrait;
use mako\syringe\Container;

/**
 * Reactor.
 *
 * @author  Frederic G. Østby
 */
class Reactor
{
	use SuggestionTrait;

	/**
	 * Input.
	 *
	 * @var \mako\cli\input\Input
	 */
	protected $input;

	/**
	 * Output.
	 *
	 * @var \mako\cli\output\Output
	 */
	protected $output;

	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Commands.
	 *
	 * @var array
	 */
	protected $commands = [];

	/**
	 * Options.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Logo.
	 *
	 * @var string
	 */
	protected $logo;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\cli\input\Input     $input       Input
	 * @param   \mako\cli\output\Output   $output      Output
	 * @param   \mako\syringe\Container   $container   Container
	 * @param   \mako\reactor\Dispatcher  $dispatcher  Command dispatcher
	 */
	public function __construct(Input $input, Output $output, Container $container = null, Dispatcher $dispatcher = null)
	{
		$this->input = $input;

		$this->output = $output;

		$this->container = $container ?? new Container;

		$this->dispatcher = $dispatcher ?? new Dispatcher($this->container);
	}

	/**
	 * Registers a command.
	 *
	 * @access  public
	 * @param   string  $command  Command
	 * @param   string  $class    Command class
	 */
	public function registerCommand(string $command, string $class)
	{
		$this->commands[$command] = $class;
	}

	/**
	 * Register a custom reactor option.
	 *
	 * @access  public
	 * @param   string    $name         Option name
	 * @param   string    $description  Option description
	 * @param   \Closure  $handler      Option handler
	 */
	public function registerCustomOption(string $name, string $description, Closure $handler)
	{
		$this->options[$name] = ['description' => $description, 'handler' => $handler];
	}

	/**
	 * Sets the reactor logo.
	 *
	 * @access  public
	 * @param   string  $logo  ASCII logo
	 */
	public function setLogo(string $logo)
	{
		$this->logo = $logo;
	}

	/**
	 * Handles custom reactor options.
	 *
	 * @access  protected
	 */
	protected function handleCustomOptions()
	{
		foreach($this->options as $name => $option)
		{
			$input = $this->input->getArgument($name);

			if(!empty($input))
			{
				$handler = $option['handler'];

				$this->container->call($handler, ['option' => $input]);
			}
		}
	}

	/**
	 * Draws information table.
	 *
	 * @access  protected
	 * @param   string     $heading  Table heading
	 * @param   array      $headers  Table headers
	 * @param   array      $rows     Table rows
	 */
	protected function drawTable(string $heading, array $headers, array $rows)
	{
		if(!empty($rows))
		{
			$this->output->write(PHP_EOL);

			$this->output->writeLn('<yellow>' . $heading . '</yellow>');

			$this->output->write(PHP_EOL);

			$table = new Table($this->output);

			$headers = array_map(function($value){ return '<green>' . $value . '</green>'; }, $headers);

			$table->draw($headers, $rows);
		}
	}

	/**
	 * Returns an array of option information.
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function getOptions(): array
	{
		$options = [];

		foreach($this->options as $name => $option)
		{
			$options[] = ['--' . $name, $option['description']];
		}

		sort($options);

		return $options;
	}

	/**
	 * Displays reactor options of there are any.
	 *
	 * @access  protected
	 */
	protected function listOptions()
	{
		$options = $this->getOptions();

		$this->drawTable('Global options:', ['Option', 'Description'], $options);
	}

	/**
	 * Displays basic reactor information.
	 *
	 * @access  protected
	 */
	protected function displayReactorInfo()
	{
		// Display basic reactor information

		if(!empty($this->logo))
		{
			$this->output->writeLn($this->logo);

			$this->output->write(PHP_EOL);
		}

		$this->output->writeLn('<yellow>Usage:</yellow>');

		$this->output->write(PHP_EOL);

		$this->output->writeLn('php reactor [command] [arguments] [options]');

		// Display reactor options if there are any

		$this->listOptions();
	}

	/**
	 * Returns an array of command information.
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function getCommands(): array
	{
		$info = [];

		foreach($this->commands as $name => $class)
		{
			$info[$name] = [$name, $this->container->get($class)->getCommandDescription()];
		}

		ksort($info);

		return $info;
	}

	/**
	 * Lists available commands if there are any.
	 *
	 * @access  protected
	 */
	protected function listCommands()
	{
		$commands = $this->getCommands();

		$this->drawTable('Available commands:', ['Command', 'Description'], $commands);
	}

	/**
	 * Dispatches a command.
	 *
	 * @access  protected
	 * @param   string     $command  Command
	 */
	protected function dispatch(string $command)
	{
		if(!isset($this->commands[$command]))
		{
			$message = 'Unknown command [ ' . $command . ' ].';

			if(($suggestion = $this->suggest($command, array_keys($this->commands))) !== null)
			{
				$message .= ' Did you mean [ ' . $suggestion . ' ]?';
			}

			$this->output->writeLn('<red>' . $message . '</red>');

			$this->listCommands();

			return;
		}

		$this->dispatcher->dispatch($this->commands[$command], $this->input->getArguments());
	}

	/**
	 * Run the reactor.
	 *
	 * @access  public
	 */
	public function run()
	{
		$this->handleCustomOptions();

		if(($command = $this->input->getArgument(1)) === null)
		{
			$this->displayReactorInfo();

			$this->listCommands();
		}
		else
		{
			try
			{
				$this->dispatch($command);
			}
			catch(InvalidOptionException $e)
			{
				$message = 'Invalid option [ ' . $e->getName() . ' ].';

				if(($suggestion = $e->getSuggestion()) !== null)
				{
					$message .= ' Did you mean [ ' . $suggestion . ' ]?';
				}

				$this->output->errorLn('<red>' . $message . '</red>');
			}
			catch(InvalidArgumentException $e)
			{
				$this->output->errorLn('<red>Invalid argument [ ' . $e->getName() . ' ].</red>');
			}
			catch(MissingOptionException $e)
			{
				$this->output->errorLn('<red>Missing required option [ ' . $e->getName() . ' ].</red>');
			}
			catch(MissingArgumentException $e)
			{
				$this->output->errorLn('<red>Missing required argument [ ' . $e->getName() . ' ].</red>');
			}
		}
	}
}