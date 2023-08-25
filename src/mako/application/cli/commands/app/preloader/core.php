<?php

return
[
	mako\application\Application::class,
	mako\application\services\BusService::class,
	mako\application\services\CacheService::class,
	mako\application\services\CryptoService::class,
	mako\application\services\DatabaseService::class,
	mako\application\services\GatekeeperService::class,
	mako\application\services\HTTPService::class,
	mako\application\services\HumanizerService::class,
	mako\application\services\I18nService::class,
	mako\application\services\LoggerService::class,
	mako\application\services\PaginationFactoryService::class,
	mako\application\services\RedisService::class,
	mako\application\services\Service::class,
	mako\application\services\SessionService::class,
	mako\application\services\SignerService::class,
	mako\application\services\ValidatorFactoryService::class,
	mako\application\services\ViewFactoryService::class,
	mako\application\services\web\ErrorHandlerService::class,
	mako\application\web\Application::class,
	mako\bus\command\CommandBus::class,
	mako\bus\command\CommandBusInterface::class,
	mako\bus\event\EventBus::class,
	mako\bus\event\EventBusInterface::class,
	mako\bus\HandlerInterface::class,
	mako\bus\query\QueryBus::class,
	mako\bus\query\QueryBusInterface::class,
	mako\bus\traits\ResolveHandlerTrait::class,
	mako\bus\traits\SingleHandlerTrait::class,
	mako\cache\CacheManager::class,
	mako\cache\stores\IncrementDecrementInterface::class,
	mako\cache\stores\Store::class,
	mako\cache\stores\StoreInterface::class,
	mako\chrono\TimeInterface::class,
	mako\classes\ClassInspector::class,
	mako\common\AdapterManager::class,
	mako\common\ConnectionManager::class,
	mako\common\traits\ConfigurableTrait::class,
	mako\common\traits\ExtendableTrait::class,
	mako\common\traits\FunctionParserTrait::class,
	mako\common\traits\NamespacedFileLoaderTrait::class,
	mako\config\Config::class,
	mako\config\loaders\Loader::class,
	mako\config\loaders\LoaderInterface::class,
	mako\database\ConnectionManager::class,
	mako\database\connections\Connection::class,
	mako\database\midgard\ORM::class,
	mako\database\midgard\Query::class,
	mako\database\midgard\relations\BelongsTo::class,
	mako\database\midgard\relations\BelongsToPolymorphic::class,
	mako\database\midgard\relations\HasMany::class,
	mako\database\midgard\relations\HasManyPolymorphic::class,
	mako\database\midgard\relations\HasOne::class,
	mako\database\midgard\relations\HasOneOrMany::class,
	mako\database\midgard\relations\HasOnePolymorphic::class,
	mako\database\midgard\relations\ManyToMany::class,
	mako\database\midgard\relations\Relation::class,
	mako\database\midgard\relations\traits\HasOneOrManyPolymorphicTrait::class,
	mako\database\midgard\ResultSet::class,
	mako\database\midgard\traits\NullableTrait::class,
	mako\database\midgard\traits\OptimisticLockingTrait::class,
	mako\database\midgard\traits\ReadOnlyTrait::class,
	mako\database\midgard\traits\TimestampedTrait::class,
	mako\database\query\compilers\Compiler::class,
	mako\database\query\compilers\traits\JsonPathBuilderTrait::class,
	mako\database\query\helpers\Helper::class,
	mako\database\query\helpers\HelperInterface::class,
	mako\database\query\Join::class,
	mako\database\query\Query::class,
	mako\database\query\Raw::class,
	mako\database\query\Result::class,
	mako\database\query\ResultSet::class,
	mako\database\query\Subquery::class,
	mako\error\ErrorHandler::class,
	mako\file\FileSystem::class,
	mako\gatekeeper\adapters\Adapter::class,
	mako\gatekeeper\adapters\AdapterInterface::class,
	mako\gatekeeper\adapters\WithGroupsInterface::class,
	mako\gatekeeper\authorization\AuthorizableInterface::class,
	mako\gatekeeper\authorization\Authorizer::class,
	mako\gatekeeper\authorization\AuthorizerInterface::class,
	mako\gatekeeper\authorization\http\routing\traits\AuthorizationTrait::class,
	mako\gatekeeper\authorization\policies\Policy::class,
	mako\gatekeeper\authorization\policies\PolicyInterface::class,
	mako\gatekeeper\authorization\traits\AuthorizableTrait::class,
	mako\gatekeeper\entities\group\GroupEntityInterface::class,
	mako\gatekeeper\entities\user\MemberInterface::class,
	mako\gatekeeper\entities\user\UserEntityInterface::class,
	mako\gatekeeper\Gatekeeper::class,
	mako\gatekeeper\repositories\group\GroupRepositoryInterface::class,
	mako\gatekeeper\repositories\user\UserRepositoryInterface::class,
	mako\http\Request::class,
	mako\http\request\Cookies::class,
	mako\http\request\Files::class,
	mako\http\request\Headers::class,
	mako\http\request\Parameters::class,
	mako\http\request\Server::class,
	mako\http\Response::class,
	mako\http\response\builders\JSON::class,
	mako\http\response\builders\ResponseBuilderInterface::class,
	mako\http\response\Cookies::class,
	mako\http\response\Headers::class,
	mako\http\routing\constraints\ConstraintInterface::class,
	mako\http\routing\Controller::class,
	mako\http\routing\Dispatcher::class,
	mako\http\routing\middleware\MiddlewareInterface::class,
	mako\http\routing\Route::class,
	mako\http\routing\Router::class,
	mako\http\routing\Routes::class,
	mako\http\routing\traits\ControllerHelperTrait::class,
	mako\http\routing\URLBuilder::class,
	mako\i18n\I18n::class,
	mako\i18n\loaders\LoaderInterface::class,
	mako\onion\Onion::class,
	mako\pagination\Pagination::class,
	mako\pagination\PaginationFactory::class,
	mako\pagination\PaginationFactoryInterface::class,
	mako\pagination\PaginationInterface::class,
	mako\redis\Connection::class,
	mako\redis\ConnectionManager::class,
	mako\redis\Message::class,
	mako\redis\Redis::class,
	mako\security\Key::class,
	mako\security\Signer::class,
	mako\session\Session::class,
	mako\session\stores\StoreInterface::class,
	mako\syringe\Container::class,
	mako\syringe\traits\ContainerAwareTrait::class,
	mako\utility\Arr::class,
	mako\utility\Collection::class,
	mako\utility\ip\IP::class,
	mako\utility\ip\IPv4::class,
	mako\utility\ip\IPv6::class,
	mako\utility\Str::class,
	mako\validator\input\http\routing\traits\InputValidationTrait::class,
	mako\validator\input\traits\InputValidationTrait::class,
	mako\validator\rules\I18nAwareInterface::class,
	mako\validator\rules\Rule::class,
	mako\validator\rules\RuleInterface::class,
	mako\validator\rules\traits\DoesntValidateWhenEmptyTrait::class,
	mako\validator\rules\traits\I18nAwareTrait::class,
	mako\validator\rules\traits\ValidatesWhenEmptyTrait::class,
	mako\validator\Validator::class,
	mako\validator\ValidatorFactory::class,
	mako\view\renderers\PHP::class,
	mako\view\renderers\RendererInterface::class,
	mako\view\renderers\Template::class,
	mako\view\renderers\traits\EscaperTrait::class,
	mako\view\View::class,
	mako\view\ViewFactory::class,
];
