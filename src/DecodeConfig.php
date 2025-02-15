<?php

declare(strict_types=1);

namespace Rammewerk\Http;

/**
 * Configuration for mapping request data to an entity.
 *
 * This class allows defining how request fields are mapped to entity properties,
 * which fields are required, and which fields should be excluded from hydration.
 */
class DecodeConfig {

    /** @var array<string, string> */
    private array $assign_list = [];
    /** @var array<string, true> */
    private array $require_list = [];
    /** @var array<string, true> */
    private array $exclude_list = [];



    /**
     * Assigns an input key from the request to a specific entity property.
     *
     * @param string $input_key     The request key to map.
     * @param string $property_name The corresponding entity property name.
     */
    public function assign(string $input_key, string $property_name): void {
        $this->assign_list[$property_name] = $input_key;
    }



    /**
     * Marks an entity property as required.
     * If missing from the request, an exception should be thrown.
     *
     * @param string $property_name The entity property that is required.
     */
    public function require(string $property_name): void {
        $this->require_list[$property_name] = true;
    }



    /**
     * Excludes a property from being set during hydration.
     *
     * @param string $property_name The property to be excluded.
     */
    public function exclude(string $property_name): void {
        $this->exclude_list[$property_name] = true;
    }



    /**
     * Retrieves the configured settings for entity mapping.
     *
     * 0: array<string, string>, // Assigned input keys to entity properties.
     * 1: array<string, true>,   // Required entity properties.
     * 2: array<string, true>    // Excluded entity properties.
     *
     * @return array{0:array<string, string>,1:array<string,true>,2:array<string,true>}
     */
    public function getSettings(): array {
        return [$this->assign_list, $this->require_list, $this->exclude_list];
    }



}