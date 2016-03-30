<?php

/**
 * TranslationModule config.
 */
return [
// <editor-fold defaultstate="collapsed" desc="asset_manager">
    'asset_manager' => [
        'resolver_configs' => [
            'paths' => [__DIR__.'/../public'],
            'map'   => [],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="doctrine">
    'doctrine' => [
        'driver' => [
            'translation_entities' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__.'/../src/Entity'],
            ],
            'orm_default' => [
                'drivers' => [
                    'TranslationModule\Entity' => 'translation_entities',
                ],
            ],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="listeners">
    'listeners' => [
        'TranslationModule\Service\Translation',
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="navigation">
    'navigation' => [
        'default' => [
            'administration' => [
                'label' => 'navigation.administration', // default label or none is rendered
                'uri'   => '#', // we need either a route or an URI to avoid fatal error
                'order' => 1000,
                'pages' => [
                    [
                        'label'     => 'navigation.translation',
                        'route'     => 'translation',
                        'resource'  => 'controller/TranslationModule\Controller\Index',
                        'privilege' => 'index',
                        'order'     => 900,
                        'pages'     => [
                            [
                                'label'     => 'navigation.translation.management.settings',
                                'route'     => 'translation/management/settings',
                                'resource'  => 'controller/TranslationModule\Controller\Management',
                                'privilege' => 'settings',
                                'visible'   => false,
                            ],
                            [
                                'label'     => 'navigation.translation.management.build',
                                'route'     => 'translation/management/build',
                                'resource'  => 'controller/TranslationModule\Controller\Management',
                                'privilege' => 'build',
                                'visible'   => false,
                            ],
                            [
                                'label'     => 'navigation.translation.management.export',
                                'route'     => 'translation/management/export',
                                'resource'  => 'controller/TranslationModule\Controller\Management',
                                'privilege' => 'export',
                                'visible'   => false,
                            ],
                            [
                                'label'     => 'navigation.translation.management.import',
                                'route'     => 'translation/management/import',
                                'resource'  => 'controller/TranslationModule\Controller\Management',
                                'privilege' => 'import',
                                'visible'   => false,
                            ],
                            [
                                'label'     => 'navigation.translation.string',
                                'route'     => 'translation/string',
                                'resource'  => 'controller/TranslationModule\Controller\String',
                                'privilege' => 'index',
                                'pages'     => [
                                    [
                                        'label' => 'navigation.translation.string.create',
                                        'route' => 'translation/string/create',
                                    ],
                                    [
                                        'label'   => 'navigation.translation.string.edit',
                                        'route'   => 'translation/string/edit',
                                        'visible' => false,
                                    ],
                                    [
                                        'label'   => 'navigation.translation.string.delete',
                                        'route'   => 'translation/string/delete',
                                        'visible' => false,
                                    ],
                                ],
                            ],
                            [
                                'label'   => 'navigation.translation.language',
                                'route'   => 'translation/language',
                                'visible' => false,
                                'pages'   => [
                                    [
                                        'label' => 'navigation.translation.language.create',
                                        'route' => 'translation/language/create',
                                    ],
                                    [
                                        'label'   => 'navigation.translation.language.edit',
                                        'route'   => 'translation/language/edit',
                                        'visible' => false,
                                    ],
                                    [
                                        'label'   => 'navigation.translation.language.delete',
                                        'route'   => 'translation/language/delete',
                                        'visible' => false,
                                    ],
                                ],
                            ],
                            [
                                'label'   => 'navigation.translation.module',
                                'route'   => 'translation/module',
                                'visible' => false,
                                'pages'   => [
                                    [
                                        'label' => 'navigation.translation.module.create',
                                        'route' => 'translation/module/create',
                                    ],
                                    [
                                        'label'   => 'navigation.translation.module.edit',
                                        'route'   => 'translation/module/edit',
                                        'visible' => false,
                                    ],
                                    [
                                        'label'   => 'navigation.translation.module.delete',
                                        'route'   => 'translation/module/delete',
                                        'visible' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="router">
    'router' => [
        'routes' => [
            'translation' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/translation/',
                    'defaults' => [
                        'controller' => 'TranslationModule\Controller\Index',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'string' => [
                        'type'    => 'literal',
                        'options' => [
                            'route'    => 'string/',
                            'defaults' => [
                                'controller' => 'TranslationModule\Controller\String',
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'create' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => 'create[/]',
                                    'defaults' => [
                                        'action' => 'create',
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => 'edit/[:id][/]',
                                    'constraints' => [
                                        'id' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'action' => 'edit',
                                    ],
                                ],
                            ],
                            'delete' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => 'delete/[:id][/]',
                                    'constraints' => [
                                        'id' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'action' => 'delete',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'language' => [
                        'type'    => 'literal',
                        'options' => [
                            'route'    => 'language/',
                            'defaults' => [
                                'controller' => 'TranslationModule\Controller\Language',
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'create' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => 'create[/]',
                                    'defaults' => [
                                        'action' => 'create',
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => 'edit/[:id][/]',
                                    'constraints' => [
                                        'id' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'action' => 'edit',
                                    ],
                                ],
                            ],
                            'delete' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => 'delete/[:id][/]',
                                    'constraints' => [
                                        'id' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'action' => 'delete',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'module' => [
                        'type'    => 'literal',
                        'options' => [
                            'route'    => 'module/',
                            'defaults' => [
                                'controller' => 'TranslationModule\Controller\Module',
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'create' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => 'create[/]',
                                    'defaults' => [
                                        'action' => 'create',
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => 'edit/[:id][/]',
                                    'constraints' => [
                                        'id' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'action' => 'edit',
                                    ],
                                ],
                            ],
                            'delete' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'       => 'delete/[:id][/]',
                                    'constraints' => [
                                        'id' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'action' => 'delete',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'management' => [
                        'type'    => 'literal',
                        'options' => [
                            'route'    => 'management/',
                            'defaults' => [
                                'controller' => 'TranslationModule\Controller\Management',
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'settings' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => 'settings[/]',
                                    'defaults' => [
                                        'action' => 'settings',
                                    ],
                                ],
                            ],
                            'build' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => 'build[/]',
                                    'defaults' => [
                                        'action' => 'build',
                                    ],
                                ],
                            ],
                            'export' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => 'export[/]',
                                    'defaults' => [
                                        'action' => 'export',
                                    ],
                                ],
                            ],
                            'import' => [
                                'type'    => 'segment',
                                'options' => [
                                    'route'    => 'import[/]',
                                    'defaults' => [
                                        'action' => 'import',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="service_manager">
    'service_manager' => [
        'factories' => [
            'TranslationModule\Service\Translation' => 'TranslationModule\Service\TranslationFactory',
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="translator">
    'translator' => [
        'event_manager_enabled' => true,
        'translation_dir'       => 'data/translations',
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="view_manager">
    'view_manager' => [
        'template_map' => [
            'translation-module/index/index' => __DIR__.'/../view/translation-module/index/index.phtml',
            'translation-module/moduleForm'  => __DIR__.'/../view/translation-module/partials/moduleForm.phtml',
        ],
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
    ],
// </editor-fold>
];
