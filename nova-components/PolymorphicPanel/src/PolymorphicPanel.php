<?php

declare(strict_types=1);

namespace Jpeters8889\PolymorphicPanel;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use RuntimeException;

class PolymorphicPanel extends Field
{
    public $component = 'polymorphic-panel';

    public $deferrable = true;

    protected array $fields;

    public $showOnIndex = false;

    public $showOnDetail = false;

    public $showOnCreation = true;

    public $showOnUpdate = true;

    protected PolymorphicResource $polymorphicResource;

    public function __construct($name, PolymorphicResource $resource)
    {
        parent::__construct($name);

        $this->polymorphicResource = $resource;

        $this->display('column');

        $this->fields = $this->polymorphicResource->fields();
    }

    public function display(string $direction): self
    {
        if ( ! in_array($direction, ['column', 'row'])) {
            throw new RuntimeException('Unknown direction');
        }

        return $this->withMeta(['direction' => $direction]);
    }

    public function jsonSerialize(): array
    {
        $this->withMeta(['fields' => $this->fields]);

        return parent::jsonSerialize();
    }

    public function resolve($resource, $attribute = null): void
    {
        $relationship = $resource->{$this->polymorphicResource->relationship()}()->get();

        collect($this->fields)
            ->map(function (Field $field) use ($relationship, $resource) {
                $field->setValue($this->polymorphicResource->check($field->attribute, $relationship, $resource));

                return $field;
            });
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute): void
    {
        /** @var Model $model */
        $value = collect(json_decode($request->input($requestAttribute)));

        $this->polymorphicResource->set($value, $model);
    }
}
