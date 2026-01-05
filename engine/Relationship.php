<?php

namespace Engine;

abstract class Relationship
{
    protected $parent;
    protected $related;
    protected $foreignKey;
    protected $localKey;
    protected $query;

    public function __construct($parent, $related, $foreignKey, $localKey)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        $this->query = $this->relatedQuery();
    }

    abstract protected function relatedQuery();
    abstract public function getResults();

    protected function getRelatedModel()
    {
        $related = new $this->related;
        return $related;
    }

    protected function getParentKey()
    {
        return $this->parent->getAttribute($this->localKey);
    }
}

class HasOne extends Relationship
{
    protected function relatedQuery()
    {
        return $this->getRelatedModel()->newQuery()
            ->where($this->foreignKey, $this->getParentKey());
    }

    public function getResults()
    {
        return $this->query->first();
    }

    public function with($relations)
    {
        $this->query->with($relations);
        return $this;
    }
}

class HasMany extends Relationship
{
    protected function relatedQuery()
    {
        return $this->getRelatedModel()->newQuery()
            ->where($this->foreignKey, $this->getParentKey());
    }

    public function getResults()
    {
        return $this->query->get();
    }

    public function with($relations)
    {
        $this->query->with($relations);
        return $this;
    }

    public function save($related)
    {
        $related->setAttribute($this->foreignKey, $this->getParentKey());
        return $related->save();
    }

    public function create($attributes)
    {
        $related = $this->getRelatedModel();
        $related->fill($attributes);
        $related->setAttribute($this->foreignKey, $this->getParentKey());
        $related->save();
        return $related;
    }
}

class BelongsTo extends Relationship
{
    protected $ownerKey;

    public function __construct($parent, $related, $foreignKey, $ownerKey)
    {
        $this->ownerKey = $ownerKey;
        parent::__construct($parent, $related, $foreignKey, $ownerKey);
    }

    protected function relatedQuery()
    {
        return $this->getRelatedModel()->newQuery()
            ->where($this->ownerKey, $this->parent->getAttribute($this->foreignKey));
    }

    public function getResults()
    {
        return $this->query->first();
    }

    public function with($relations)
    {
        $this->query->with($relations);
        return $this;
    }

    public function associate($related)
    {
        $this->parent->setAttribute($this->foreignKey, $related->getAttribute($this->ownerKey));
        return $this->parent->save();
    }

    public function dissociate()
    {
        $this->parent->setAttribute($this->foreignKey, null);
        return $this->parent->save();
    }
}

class HasManyThrough extends Relationship
{
    protected $through;
    protected $firstKey;
    protected $secondKey;
    protected $localKey;

    public function __construct($parent, $related, $through, $firstKey, $secondKey, $localKey)
    {
        $this->through = $through;
        $this->firstKey = $firstKey;
        $this->secondKey = $secondKey;
        $this->localKey = $localKey;
        
        $throughModel = new $through;
        $this->foreignKey = $firstKey;
        
        parent::__construct($parent, $related, $firstKey, $localKey);
    }

    protected function relatedQuery()
    {
        $throughModel = new $this->through;
        $relatedModel = $this->getRelatedModel();
        
        return $relatedModel->newQuery()
            ->join($throughModel->getTable(), $relatedModel->getTable() . '.' . $this->secondKey, '=', $throughModel->getTable() . '.' . $throughModel->getKeyName())
            ->where($throughModel->getTable() . '.' . $this->firstKey, $this->getParentKey());
    }

    public function getResults()
    {
        return $this->query->get();
    }

    public function with($relations)
    {
        $this->query->with($relations);
        return $this;
    }
}

class BelongsToMany extends Relationship
{
    protected $pivotTable;
    protected $foreignPivotKey;
    protected $relatedPivotKey;
    protected $parentKey;
    protected $relatedKey;

    public function __construct($parent, $related, $pivotTable, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey)
    {
        $this->pivotTable = $pivotTable;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;
        
        parent::__construct($parent, $related, $foreignPivotKey, $parentKey);
    }

    protected function relatedQuery()
    {
        $relatedModel = $this->getRelatedModel();
        
        return $relatedModel->newQuery()
            ->join($this->pivotTable, $relatedModel->getTable() . '.' . $this->relatedKey, '=', $this->pivotTable . '.' . $this->relatedPivotKey)
            ->where($this->pivotTable . '.' . $this->foreignPivotKey, $this->getParentKey())
            ->select($relatedModel->getTable() . '.*');
    }

    public function getResults()
    {
        return $this->query->get();
    }

    public function with($relations)
    {
        $this->query->with($relations);
        return $this;
    }

    public function attach($ids, $pivotData = [])
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $inserts = [];
        foreach ($ids as $id) {
            $insert = [
                $this->foreignPivotKey => $this->getParentKey(),
                $this->relatedPivotKey => $id,
            ];
            $inserts[] = array_merge($insert, $pivotData);
        }

        return ModelBase::table($this->pivotTable)->insert($inserts);
    }

    public function detach($ids = null)
    {
        $query = ModelBase::table($this->pivotTable)
            ->where($this->foreignPivotKey, $this->getParentKey());

        if ($ids !== null) {
            if (!is_array($ids)) {
                $ids = [$ids];
            }
            $query->whereIn($this->relatedPivotKey, $ids);
        }

        return $query->delete();
    }

    public function sync($ids, $detaching = true)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $current = ModelBase::table($this->pivotTable)
            ->where($this->foreignPivotKey, $this->getParentKey())
            ->pluck($this->relatedPivotKey);

        $currentIds = $current ? array_column($current, $this->relatedPivotKey) : [];

        $toAttach = array_diff($ids, $currentIds);
        $toDetach = $detaching ? array_diff($currentIds, $ids) : [];

        // Detach
        if (!empty($toDetach)) {
            $this->detach($toDetach);
        }

        // Attach
        if (!empty($toAttach)) {
            $this->attach($toAttach);
        }

        return [
            'attached' => $toAttach,
            'detached' => $toDetach,
            'updated' => [],
        ];
    }

    public function toggle($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $current = ModelBase::table($this->pivotTable)
            ->where($this->foreignPivotKey, $this->getParentKey())
            ->whereIn($this->relatedPivotKey, $ids)
            ->pluck($this->relatedPivotKey);

        $currentIds = $current ? array_column($current, $this->relatedPivotKey) : [];

        $toAttach = array_diff($ids, $currentIds);
        $toDetach = array_intersect($ids, $currentIds);

        // Detach
        if (!empty($toDetach)) {
            $this->detach($toDetach);
        }

        // Attach
        if (!empty($toAttach)) {
            $this->attach($toAttach);
        }

        return [
            'attached' => $toAttach,
            'detached' => $toDetach,
        ];
    }
}
