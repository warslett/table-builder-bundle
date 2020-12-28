# Table Builder Bundle
[![Build Status](https://circleci.com/gh/warslett/table-builder.png?style=shield)](https://circleci.com/gh/warslett/table-builder?branch=master)
[![codecov](https://codecov.io/gh/warslett/table-builder/branch/master/graph/badge.svg?token=TLPUHTMP2E)](https://codecov.io/gh/warslett/table-builder)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fwarslett%2Ftable-builder%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/warslett/table-builder/master)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

**THIS BUNDLE IS CURRENTLY UNDER DEVELOPMENT**

Table builder bundle provides integration with the warslett/table-builder package and the symfony framework. This bundle
will register the required services and extensions in Symfony to allow you to use the table-builder package in a
symfony project with minimal setup.

## Installation
`composer require warslett/table-builder-bundle warslett/table-builder`

## Documentation
Full documentation available [here](https://github.com/warslett/table-builder/blob/master/docs/en/index.md).

## Setup
Add the bundle to your config/bundles.php array (this will be done automatically for you if you are using symfony flex)

``` php
<?php
// config/bundles.php

return [
    ...
    WArslett\TableBuilderBundle\TableBuilderBundle::class => ['all' => true],
];
```

## Usage
Inject the TableBuilderFactoryInterface service into your controller to build a table and load data into it using an
adapter:
``` php
<?php
// src/Action/RetrieveUsers.php

namespace App\Action;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig;
use WArslett\TableBuilder\Column\TextColumn;
use WArslett\TableBuilder\DataAdapter\DoctrineOrmAdapter;
use WArslett\TableBuilder\TableBuilderFactoryInterface;

class RetrieveUsers
{
    private TableBuilderFactoryInterface $tableBuilderFactory;
    private EntityManagerInterface $entityManager;
    private Twig\Environment $twigEnvironment;

    public function __construct(
        TableBuilderFactoryInterface $tableBuilderFactory,
        EntityManagerInterface $entityManager,
        Twig\Environment $twigEnvironment
    ) {
        $this->tableBuilderFactory = $tableBuilderFactory;
        $this->entityManager = $entityManager;
        $this->twigEnvironment = $twigEnvironment;
    }

    public function __invoke(Request $request): Response
    {
        $table = $this->tableBuilderFactory->createTableBuilder()
            ->rowsPerPageOptions([10, 20, 50])
            ->defaultRowsPerPage(10)
            ->add(TextColumn::withName('email')
                ->label('Email')
                ->property('email')
                ->sortable())
            ->add(DateTimeColumn::withName('last_login')
                ->label('Last Login')
                ->property('lastLogin')
                ->format('Y-m-d H:i:s')
                ->sortable())
            ->add(ActionGroupColumn::withName('actions')
                ->label('Actions')
                ->add(ActionBuilder::withName('update')
                    ->label('Update')
                    ->route('user_update', ['id' => 'id'])) // map 'id' parameter to property path 'id'
                ->add(ActionBuilder::withName('delete')
                    ->label('Delete')
                    ->route('user_delete', ['id' => 'id'])
                    ->attribute('extra_classes', ['btn-danger'])))
            ->buildTable('users')
            ->setDataAdapter(DoctrineOrmAdapter::withQueryBuilder($this->entityManager->createQueryBuilder()
                ->select('u')
                ->from(User::class, 'u'))
                ->mapSortToggle('email', 'u.email')
                ->mapSortToggle('last_login', 'u.lastLogin'))
            ->handleSymfonyRequest($request)
        ;

        return new Response($this->twigEnvironment->render('table_page.html.twig', [
            'users_table' => $table
        ]));
    }
}
```
Then in your template you can render the table like this:
``` twig
{# templates/table_page.html.twig #}

{# the table twig function takes the table object as a parameter #}
{{ table(users_table) }}
```

Which will render your table with pagination and sorting working out the box.

![rendered table](https://github.com/warslett/table-builder/raw/master/docs/img/example.png "Rendered Html Table")

You can render two tables on the same page and they will sort and paginate independently.

## Config
``` yaml
# config/packages/table_builder.yaml
table_builder:

  twig_renderer:
    # you can change the default twig theme here
    theme_template: 'table-builder/bootstrap4.html.twig'
    
    # add custom column value template from a twig template file
    cell_value_templates:
      App\TableBuilder\Column\MyCustomColumn: 'my_custom_cell_value_template.html.twig'
      
    # add a custom column value template from a block in your theme
    cell_value_blocks:
      App\TableBuilder\Column\MyCustomColumn: 'my_custom_cell_value_block'
      
  phtml_renderer:
    # you can change the default phtml theme directory here
    theme_directory: '%kernel.project_dir%/templates/table-builder'
    
    # add custom column value template from a twig template file
    cell_value_templates:
      App\TableBuilder\Column\MyCustomColumn: '%kernel.project_dir%/templates/table-builder/my_custom_cell_value_template.phtml'

```

You can also register cell value transformers for the Csv Renderer using service tags:
```yaml
services:
  
  App\TableBuilder\Csv\MyCustomColumnAdapter:
    tags:
      - { name: 'table_builder.csv_cell_value_transformer', rendering_type: App\TableBuilder\Column\MyCustomColumn }
```

## Themeing
Create your own table theme in twig:
``` twig
{# templates/my_table_theme.html.twig #}
{% extends 'table-builder/bootstrap.4.html.twig' %} {# extend a default theme and just override the blocks you want #}

{% block table_element %}
    <table class="table table-dark"> {# Change the default bootstrap4 theme to use table-dark #}
        <thead>
        <tr>
            {% for heading in table.headings %}
                {{ table_heading(table, heading) }}
            {% endfor %}
        </tr>
        </thead>
        <tbody>
        {% for row in table.rows %}
            {{ table_row(table, row) }}
        {% endfor %}
        </tbody>
    </table>
{% endblock %}
```
Then just update the value for theme_template in your config to your new theme template

## Documentation
Full documentation will be available at the repo for the core repository https://github.com/warslett/table-builder/blob/master/README.md
