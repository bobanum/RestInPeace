<?php

namespace RestInPeace;

/**
 * Class ModelOutputTrait
 *
 * This class represents a database table or view.
 * It provides methods to interact with the database structure.
 */
trait ModelOutputTrait {
    function relationsOutput() {
        $relations = [];
        foreach ($this->relations as $relationName => $relation) {
            $type = ['has_one', 'belongs_to', 'has_many', 'belongs_to_many', 'belongs_to_through', 'has_many_through'][$relation->type];
            $relations[] = $relation->outputModel();
        }
        $relations = implode("\n", $relations);
        return $relations;
    }
    function attributesOutput() {
        $attributes = [];
        foreach ($this->columns as $columnName => $column) {
            $attributes[] = "        '{$columnName}' => '{$column['type']}',";
        }
        $attributes = implode("\n", $attributes);
        $result = <<<"EOD"
            public \$attributes = [
        {$attributes}
            ];
        EOD;
        return $result;
    }
    function modelOutput() {
        $modelName = ucfirst($this->name);
        $tableName = $this->name;
        $attributes = $this->attributesOutput();
        $relations = $this->relationsOutput();
        $output = <<<"EOD"
        <?php
        /**
         * This file was generated by RestInPeace.
         * Any changes made to this file will be overwritten when the schema is updated.
         * Use the corresponding trait file to add custom methods to this model.
         */
        namespace RestInPeace\\Models;
        use RestInPeace\\Model;
        class {$modelName} extends Model {
            use Traits\\{$modelName}Trait;
        {$attributes}
        {$relations}
        }
        EOD;
        return $output;
    }
    function traitOutput() {
        $modelName = ucfirst($this->name);
        $output = <<<"EOD"
        <?php
        namespace RestInPeace\\Models\\Traits;
        trait {$modelName}Trait {
            protected \$hidden = ['created_at', 'updated_at'];
            protected \$fillable = [];
            protected \$casts = [];
            public \$with = [];
            public \$appends = [];
        }
        EOD;
        return $output;
    }
}