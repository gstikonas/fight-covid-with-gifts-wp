<?php namespace CovidGifts\App\Abstracts;

use CovidGifts\App\Container;
use CovidGifts\App\Contracts\Database;

abstract class AbstractModel {

    protected $table;

    protected $attributes;

    public function __construct($attributes = [])
    {
        if (!isset($this->table) || !$this->table) {
            throw new \Exception('The model db table has not been set');
        }
        $this->setAttributes($attributes);
    }

    public function delete()
    {
        return $this->db->delete($this->id);
    }

    public function save()
    {
        return $this->id ? $this->update() : $this->create();
    }

    public function create($attributes = null)
    {
        $this->db->create($attributes ?: $this->getAttributesForDb());
        if ($attributes) {
            $this->setAttributes($attributes);
        }
        return $this;
    }

    public function update($attributes = null)
    {
        $this->db->update($attributes ?: $this->getAttributesForDb());
        if ($attributes) {
            $this->setAttributes($attributes);
        }
        return $this;
    }

    public function __get($attribute)
    {
        $value = isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
        $method = 'get'.ucfirst($attribute).'Attribute';
        if (method_exists($this, $method)) {
            return $this->$method($value);
        } else {
            return $value;
        }
    }

    public function __set($attribute, $value)
    {
        $method = 'set'.ucfirst($attribute).'Attribute';
        if (method_exists($this, $method)) {
            return $this->$method($value);
        } else {
            return $this->attributes[$attribute] = $value;
        }
    }

    public function setAttributes($attributes)
    {
        foreach($attributes as $key => $val) {
            $this->$key = $val;
        }
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttributesForDb()
    {
        return $this->attributes;
    }

    protected static function DB()
    {
        $c = app()->container->getResolver(Database::class);
        return new $c(static::getTable());
    }

    public static function all()
    {
        $results = static::DB()->all();
        $models = array_map( function ($result) { 
            return new static($result);
        }, $results);
        return $models;
    }

    public static function find($id)
    {
        $result = static::DB()->find($id);
        return $result ? new static( $result ) : null;
    }

    public static function getTable()
    {
        $c = new static;
        return $c->table;
    }

    public static function buildCsvString() {

        $csv_output = '';
        $class = get_called_class();

        $fake = new $class;
        $fields = array_keys( $fake->getAttributes() );
        foreach ( $fields as $field ) {
            $csv_output .= strtoupper($field).", ";
        }
        $csv_output .= "\n";

        $models = $class::all();
        foreach ( $models as $row ) {
            foreach ( $fields as $field ) {
                # Replace commas that are inside the fields
                $value = str_replace(',', '%x2C', $row->$field );
                $csv_output .= $value . ", ";
            }
            $csv_output .= "\n";
        }
        return $csv_output;

    }

    public static function buildCsvArray() {

        $csv_array = array();
        $class = get_called_class();

        $fake = new $class;
        $fields = array_keys( $fake->getAttributes() );
        $fields = array_map('strtoupper', $fields);

        $csv_array[] = $fields;

        $models = $class::all();
        foreach ( $models as $model ) {
            $csv_array[] = $model->getAttributes();
        }
        return $csv_array;
    }
}