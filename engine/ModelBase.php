<?php

namespace Engine;

use PDO;
use PDOException;

// Include relationship classes
require_once __DIR__ . '/Relationship.php';

class ModelBase
{
    protected static $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $timestamps = true;
    protected $softDeletes = false;
    
    // Timestamp columns
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';
    
    // Model attributes
    protected $attributes = [];
    protected $original = [];
    protected $exists = false;

    public function __construct($attributes = [])
    {
        if (!self::$db) {
            $this->connect();
        }
        
        $this->fill($attributes);
        
        // Set table name if not specified
        if (empty($this->table)) {
            $className = static::class;
            $this->table = strtolower(preg_replace('/([A-Z])/', '_$1', lcfirst(basename(str_replace('\\', '/', $className)))));
        }
    }

    private function connect()
    {
        $config = \Engine\Boot::config('database');
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            self::$db = new PDO($dsn, $config['username'], $config['password']);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Database Connection Error: " . $e->getMessage());
        }
    }

    // Static query methods
    public static function query()
    {
        $instance = new static();
        return new QueryBuilder($instance->getTable(), self::$db);
    }

    public static function table($table)
    {
        return new QueryBuilder($table, self::$db);
    }

    // Find methods
    public static function find($id)
    {
        $instance = new static();
        $result = $instance->newQuery()->where($instance->primaryKey, $id)->first();
        
        if ($result) {
            $instance->fill($result);
            $instance->exists = true;
            $instance->original = $result;
            return $instance;
        }
        
        return null;
    }

    public static function findOrFail($id)
    {
        $model = static::find($id);
        if (!$model) {
            throw new \Exception("Model not found with ID: $id");
        }
        return $model;
    }

    public static function findOrNew($id)
    {
        $model = static::find($id);
        return $model ?: new static();
    }

    public static function all()
    {
        $instance = new static();
        $results = $instance->newQuery()->get();
        
        $models = [];
        foreach ($results as $result) {
            $model = new static();
            $model->fill($result);
            $model->exists = true;
            $model->original = $result;
            $models[] = $model;
        }
        
        return $models;
    }

    public static function where($column, $operator = null, $value = null)
    {
        return static::query()->where($column, $operator, $value);
    }

    public static function whereIn($column, $values)
    {
        return static::query()->whereIn($column, $values);
    }

    public static function orderBy($column, $direction = 'ASC')
    {
        return static::query()->orderBy($column, $direction);
    }

    public static function limit($limit)
    {
        return static::query()->limit($limit);
    }

    public static function first()
    {
        $instance = new static();
        $result = $instance->newQuery()->first();
        
        if ($result) {
            $instance->fill($result);
            $instance->exists = true;
            $instance->original = $result;
            return $instance;
        }
        
        return null;
    }

    public static function count()
    {
        $instance = new static();
        return $instance->newQuery()->count();
    }

    public static function exists()
    {
        $instance = new static();
        return $instance->newQuery()->exists();
    }

    // Instance methods
    public function getTable()
    {
        return $this->table;
    }

    public function getKeyName()
    {
        return $this->primaryKey;
    }

    public function getKey()
    {
        return $this->getAttribute($this->primaryKey);
    }

    public function newQuery()
    {
        $query = new QueryBuilder($this->table, self::$db);
        
        // Apply soft deletes if enabled
        if ($this->softDeletes) {
            $query->whereNull($this->table . '.' . self::DELETED_AT);
        }
        
        return $query;
    }

    // Attribute methods
    public function getAttribute($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function fill($attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        
        if (!$this->exists) {
            $this->original = $this->attributes;
        }
        
        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getOriginal($key = null)
    {
        if ($key === null) {
            return $this->original;
        }
        
        return $this->original[$key] ?? null;
    }

    public function isDirty($attribute = null)
    {
        if ($attribute === null) {
            return $this->attributes !== $this->original;
        }
        
        return $this->getAttribute($attribute) !== $this->getOriginal($attribute);
    }

    public function isClean($attribute = null)
    {
        return !$this->isDirty($attribute);
    }

    // Magic methods
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

    // Save methods
    public function save()
    {
        $this->fireModelEvent('saving');
        
        if ($this->exists) {
            $saved = $this->performUpdate();
        } else {
            $saved = $this->performInsert();
        }
        
        if ($saved) {
            $this->fireModelEvent('saved');
            $this->exists = true;
            $this->original = $this->attributes;
        }
        
        return $saved;
    }

    protected function performInsert()
    {
        $attributes = $this->getAttributes();
        
        // Add timestamps if enabled
        if ($this->timestamps) {
            $attributes[self::CREATED_AT] = $this->freshTimestamp();
            $attributes[self::UPDATED_AT] = $this->freshTimestamp();
        }
        
        $success = $this->newQuery()->insert($attributes);
        
        if ($success && $this->primaryKey === 'id') {
            $this->setAttribute($this->primaryKey, self::$db->lastInsertId());
        }
        
        return $success;
    }

    protected function performUpdate()
    {
        $attributes = $this->getDirtyAttributes();
        
        if (empty($attributes)) {
            return true;
        }
        
        // Add updated timestamp if enabled
        if ($this->timestamps) {
            $attributes[self::UPDATED_AT] = $this->freshTimestamp();
        }
        
        return $this->newQuery()
            ->where($this->primaryKey, $this->getKey())
            ->update($attributes);
    }

    public function getDirtyAttributes()
    {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if ($this->isDirty($key)) {
                $dirty[$key] = $value;
            }
        }
        
        return $dirty;
    }

    // Delete methods
    public function delete()
    {
        if (!$this->exists) {
            return false;
        }
        
        $this->fireModelEvent('deleting');
        
        if ($this->softDeletes) {
            return $this->performSoftDelete();
        }
        
        $success = $this->performDelete();
        
        if ($success) {
            $this->fireModelEvent('deleted');
            $this->exists = false;
        }
        
        return $success;
    }

    protected function performDelete()
    {
        return $this->newQuery()
            ->where($this->primaryKey, $this->getKey())
            ->delete();
    }

    protected function performSoftDelete()
    {
        $this->setAttribute(self::DELETED_AT, $this->freshTimestamp());
        $success = $this->save();
        
        if ($success) {
            $this->fireModelEvent('deleted');
            $this->exists = false;
        }
        
        return $success;
    }

    public static function destroy($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        
        $count = 0;
        foreach ($ids as $id) {
            $model = static::find($id);
            if ($model && $model->delete()) {
                $count++;
            }
        }
        
        return $count;
    }

    // Soft delete methods
    public static function withTrashed()
    {
        $instance = new static();
        $query = new QueryBuilder($instance->table, self::$db);
        
        // Remove soft delete filter
        return $query;
    }

    public static function onlyTrashed()
    {
        $instance = new static();
        return $instance->newQuery()
            ->whereNotNull($instance->table . '.' . self::DELETED_AT);
    }

    public function restore()
    {
        if (!$this->softDeletes || !$this->getAttribute(self::DELETED_AT)) {
            return false;
        }
        
        $this->setAttribute(self::DELETED_AT, null);
        return $this->save();
    }

    public function forceDelete()
    {
        if (!$this->exists) {
            return false;
        }
        
        $this->fireModelEvent('deleting');
        $success = $this->performDelete();
        
        if ($success) {
            $this->fireModelEvent('deleted');
            $this->exists = false;
        }
        
        return $success;
    }

    // Utility methods
    protected function freshTimestamp()
    {
        return date('Y-m-d H:i:s');
    }

    protected function fireModelEvent($event)
    {
        // Placeholder for event system
        // Can be extended later with proper event handling
    }

    // Legacy methods for backward compatibility
    public function rawQuery($sql, $params = [])
    {
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll($sql, $params = [])
    {
        return $this->rawQuery($sql, $params)->fetchAll();
    }

    public function fetch($sql, $params = [])
    {
        return $this->rawQuery($sql, $params)->fetch();
    }

    // Mass assignment
    public static function create($attributes)
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public static function insert($data)
    {
        $instance = new static();
        return $instance->newQuery()->insert($data);
    }

    public static function update($data)
    {
        $instance = new static();
        return $instance->newQuery()->update($data);
    }

    // Relationships
    protected function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? $this->primaryKey;
        
        return new HasOne($this, $related, $foreignKey, $localKey);
    }

    protected function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? $this->primaryKey;
        
        return new HasMany($this, $related, $foreignKey, $localKey);
    }

    protected function belongsTo($related, $foreignKey = null, $ownerKey = null)
    {
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $ownerKey = $ownerKey ?? (new $related)->getKeyName();
        
        return new BelongsTo($this, $related, $foreignKey, $ownerKey);
    }

    protected function hasManyThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null)
    {
        $throughModel = new $through;
        $relatedModel = new $related;
        
        $firstKey = $firstKey ?? $this->getForeignKey();
        $secondKey = $secondKey ?? $throughModel->getForeignKey();
        $localKey = $localKey ?? $this->primaryKey;
        
        return new HasManyThrough($this, $related, $through, $firstKey, $secondKey, $localKey);
    }

    protected function belongsToMany($related, $pivotTable = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null)
    {
        $pivotTable = $pivotTable ?? $this->getPivotTable($related);
        $foreignPivotKey = $foreignPivotKey ?? $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?? (new $related)->getForeignKey();
        $parentKey = $parentKey ?? $this->primaryKey;
        $relatedKey = $relatedKey ?? (new $related)->getKeyName();
        
        return new BelongsToMany($this, $related, $pivotTable, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey);
    }

    protected function getPivotTable($related)
    {
        $relatedModel = new $related;
        $tables = [$this->table, $relatedModel->getTable()];
        sort($tables);
        return implode('_', $tables);
    }

    protected function getForeignKey()
    {
        return strtolower(basename(str_replace('\\', '/', static::class))) . '_id';
    }

    // Dynamic relationship loading
    public function __call($method, $parameters)
    {
        // Check if method is a relationship method
        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        }
        
        throw new \Exception("Method {$method} does not exist.");
    }

    // Load relationships
    protected $relations = [];
    protected $loadedRelations = [];

    public function load($relations)
    {
        if (is_string($relations)) {
            $relations = [$relations];
        }

        foreach ($relations as $relation) {
            if (!isset($this->loadedRelations[$relation])) {
                $this->loadedRelations[$relation] = $this->$relation()->getResults();
            }
        }

        return $this;
    }

    public function getRelation($relation)
    {
        return $this->loadedRelations[$relation] ?? null;
    }

    public function setRelation($relation, $value)
    {
        $this->loadedRelations[$relation] = $value;
        return $this;
    }

    public function with($relations)
    {
        $this->load($relations);
        return $this;
    }
}
