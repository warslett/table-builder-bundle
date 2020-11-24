# Table Builder Bundle
**THIS BUNDLE IS CURRENTLY UNDER DEVELOPMENT**

Table builder bundle provides integration with the warslett/table-builder package and the symfony framework. This bundle
will register the required services and extensions in Symfony to allow you to use the table-builder package in a
symfony project with minimal setup.

## Installation
`composer require warslett/table-builder-bundle`

## Setup
Add the bundle to your config/bundles.php array

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
use WArslett\TableBuilder\RequestAdapter\SymfonyHttpAdapter;
use WArslett\TableBuilder\TableBuilderFactoryInterface;
use WArslett\TableBuilder\ValueAdapter\PropertyAccessAdapter;

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
            ->setRowsPerPageOptions([10, 20, 50])
            ->setDefaultRowsPerPage(10)
            ->addColumn(TextColumn::withName('full_name')
                ->setLabel('Full Name')
                ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('fullName')))
            ->addColumn(TextColumn::withName('email')
                ->setLabel('Email')
                ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('email')))
            ->addColumn(TextColumn::withName('manager')
                ->setLabel("Manager's Name")
                ->setValueAdapter(PropertyAccessAdapter::withPropertyPath('manager.fullName')))
            ->buildTable('users')
            ->setDataAdapter(DoctrineOrmAdapter::withQueryBuilder($this->entityManager->createQueryBuilder()
                ->select('u')
                ->from(User::class, 'u')
            ))
            ->handleRequest(SymfonyHttpAdapter::withRequest($request))
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
Which will render your table with pagination and sorting working out the box. You can render two tables on the same page
and they will sort and paginate independently.

## Config
``` yaml
# config/packages/table_builder.yaml
table_builder:

  twig_renderer:
    theme_template: 'table-builder/bootstrap4.html.twig' # you can change the default twig theme here
    cell_value_templates:
      App\TableBuilder\Column\MyCustomColumn: 'my_custom_block.html.twig' # add a custom column template

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
