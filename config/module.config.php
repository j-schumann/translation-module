<?php
/**
 * TranslationModule config
 */
return array(
    'doctrine' => array(
        'driver' => array(
            'translation_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/TranslationModule/Entity')
            ),

            'orm_default' => array(
                'drivers' => array(
                    'TranslationModule\Entity' => 'translation_entities'
                ),
            ),
        ),
    ),

    'navigation' => array(
        'default' => array(
            array(
                'label'     => 'navigation.translation',
                'route'     => 'translation',
                'resource'  => 'controller/TranslationModule\Controller\Index',
                'privilege' => 'index',
                'order'     => 1000,
                'pages' => array(
                    array(
                        'label'     => 'navigation.translation.string',
                        'route'     => 'translation/string',
                        'resource'  => 'controller/TranslationModule\Controller\String',
                        'privilege' => 'index',
                        'pages' => array(
                            array(
                                'label' => 'navigation.translation.string.create',
                                'route' => 'translation/string/create',
                            ),
                            array(
                                'label'   => 'navigation.translation.string.edit',
                                'route'   => 'translation/string/edit',
                                'visible' => false,
                            ),
                            array(
                                'label'   => 'navigation.translation.string.delete',
                                'route'   => 'translation/string/delete',
                                'visible' => false,
                            ),
                        ),
                    ),
                    array(
                        'label' => 'navigation.translation.language',
                        'route' => 'translation/language',
                        'pages' => array(
                            array(
                                'label' => 'navigation.translation.language.create',
                                'route' => 'translation/language/create',
                            ),
                            array(
                                'label'   => 'navigation.translation.language.edit',
                                'route'   => 'translation/language/edit',
                                'visible' => false,
                            ),
                            array(
                                'label'   => 'navigation.translation.language.delete',
                                'route'   => 'translation/language/delete',
                                'visible' => false,
                            ),
                        ),
                    ),
                    array(
                        'label' => 'navigation.translation.module',
                        'route' => 'translation/module',
                        'pages' => array(
                            array(
                                'label' => 'navigation.translation.module.create',
                                'route' => 'translation/module/create',
                            ),
                            array(
                                'label'   => 'navigation.translation.module.edit',
                                'route'   => 'translation/module/edit',
                                'visible' => false,
                            ),
                            array(
                                'label'   => 'navigation.translation.module.delete',
                                'route'   => 'translation/module/delete',
                                'visible' => false,
                            ),
                        ),
                    ),
                    array(
                        'label' => 'navigation.translation.management',
                        'route' => 'translation/management',
                        'pages' => array(
                            array(
                                'label' => 'navigation.translation.management.build',
                                'route' => 'translation/management/build',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'router' => array(
        'routes' => array(
            'translation' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/translation/',
                    'defaults' => array(
                        '__NAMESPACE__' => 'TranslationModule\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'string' => array(
                        'type'    => 'literal',
                        'options' => array(
                            'route'    => 'string/',
                            'defaults' => array(
                                'controller' => 'String',
                                'action'     => 'index'
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'create' => array(
                                'type'    => 'segment',
                                'options' => array(
                                    'route'    => 'create[/]',
                                    'defaults' => array(
                                        'action'     => 'create'
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type'    => 'segment',
                                'options' => array(
                                    'route'       => 'edit/[:id][/]',
                                    'constraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'action' => 'edit'
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type'    => 'segment',
                                'options' => array(
                                    'route'       => 'delete/[:id][/]',
                                    'constraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'action' => 'delete'
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'language' => array(
                        'type'    => 'literal',
                        'options' => array(
                            'route'    => 'language/',
                            'defaults' => array(
                                'controller' => 'Language',
                                'action'     => 'index'
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'create' => array(
                                'type'    => 'segment',
                                'options' => array(
                                    'route'    => 'create[/]',
                                    'defaults' => array(
                                        'action'     => 'create'
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type'    => 'segment',
                                'options' => array(
                                    'route'       => 'edit/[:id][/]',
                                    'constraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'action' => 'edit'
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type'    => 'segment',
                                'options' => array(
                                    'route'       => 'delete/[:id][/]',
                                    'constraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'action' => 'delete'
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'module' => array(
                        'type'    => 'literal',
                        'options' => array(
                            'route'    => 'module/',
                            'defaults' => array(
                                'controller' => 'Module',
                                'action'     => 'index'
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'create' => array(
                                'type'    => 'segment',
                                'options' => array(
                                    'route'    => 'create[/]',
                                    'defaults' => array(
                                        'action'     => 'create'
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type'    => 'segment',
                                'options' => array(
                                    'route'       => 'edit/[:id][/]',
                                    'constraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'action' => 'edit'
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type'    => 'segment',
                                'options' => array(
                                    'route'       => 'delete/[:id][/]',
                                    'constraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'action' => 'delete'
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'management' => array(
                        'type'    => 'literal',
                        'options' => array(
                            'route'    => 'management/',
                            'defaults' => array(
                                'controller' => 'Management',
                                'action'     => 'index'
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'build' => array(
                                'type'    => 'segment',
                                'options' => array(
                                    'route'    => 'build[/]',
                                    'defaults' => array(
                                        'action'     => 'build'
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'TranslationModule\Controller\Index'      => 'TranslationModule\Controller\IndexController',
            'TranslationModule\Controller\Language'   => 'TranslationModule\Controller\LanguageController',
            'TranslationModule\Controller\Management' => 'TranslationModule\Controller\ManagementController',
            'TranslationModule\Controller\Module'     => 'TranslationModule\Controller\ModuleController',
            'TranslationModule\Controller\String'     => 'TranslationModule\Controller\StringController',
        ),
    ),

    'service_manager' => array(
        'factories' => array(
            'Navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'TranslationModule\Service\Translation' => 'TranslationModule\Service\TranslationFactory',
        ),
    ),

    'translator' => array(
        'event_manager_enabled' => true,
        'translation_dir'       => 'data/translations',
    ),

    'view_manager' => array(
        'template_map' => array(
            'translation-module/index/index' => __DIR__ . '/../view/translation-module/index/index.phtml',
            'translation-module/moduleForm'  => __DIR__ . '/../view/translation-module/partials/moduleForm.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
