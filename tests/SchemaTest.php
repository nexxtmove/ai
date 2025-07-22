<?php

use Nexxtmove\Schema;

describe('JSON Schema Kit', function () {
    describe('string()', function () {
        test('should create basic string schema', function () {
            $result = Schema::string();
            expect($result)->toEqual([
                'type' => 'string',
            ]);
        });

        test('should accept additional properties', function () {
            $result = Schema::string([
                'description' => 'A name field',
                'pattern' => '^[A-Za-z]+$',
                'format' => 'email',
            ]);
            expect($result)->toEqual([
                'type' => 'string',
                'description' => 'A name field',
                'pattern' => '^[A-Za-z]+$',
                'format' => 'email',
            ]);
        });

        test('should preserve custom properties', function () {
            $result = Schema::string([
                'title' => 'Name',
                'const' => 'fixed-value',
                'enum' => ['option1', 'option2'],
            ]);
            expect($result)->toEqual([
                'type' => 'string',
                'title' => 'Name',
                'const' => 'fixed-value',
                'enum' => ['option1', 'option2'],
            ]);
        });
    });

    describe('number()', function () {
        test('should create basic number schema', function () {
            $result = Schema::number();
            expect($result)->toEqual([
                'type' => 'number',
            ]);
        });

        test('should accept additional properties', function () {
            $result = Schema::number([
                'description' => 'Price in dollars',
                'minimum' => 0,
                'maximum' => 1000,
                'multipleOf' => 0.01,
            ]);
            expect($result)->toEqual([
                'type' => 'number',
                'description' => 'Price in dollars',
                'minimum' => 0,
                'maximum' => 1000,
                'multipleOf' => 0.01,
            ]);
        });

        test('should handle exclusive bounds', function () {
            $result = Schema::number([
                'exclusiveMinimum' => 0,
                'exclusiveMaximum' => 100,
            ]);
            expect($result)->toEqual([
                'type' => 'number',
                'exclusiveMinimum' => 0,
                'exclusiveMaximum' => 100,
            ]);
        });
    });

    describe('integer()', function () {
        test('should create basic integer schema', function () {
            $result = Schema::integer();
            expect($result)->toEqual([
                'type' => 'integer',
            ]);
        });

        test('should accept additional properties', function () {
            $result = Schema::integer([
                'description' => 'Age in years',
                'minimum' => 0,
                'maximum' => 120,
            ]);
            expect($result)->toEqual([
                'type' => 'integer',
                'description' => 'Age in years',
                'minimum' => 0,
                'maximum' => 120,
            ]);
        });
    });

    describe('boolean()', function () {
        test('should create basic boolean schema', function () {
            $result = Schema::boolean();
            expect($result)->toEqual([
                'type' => 'boolean',
            ]);
        });

        test('should accept additional properties', function () {
            $result = Schema::boolean([
                'description' => 'Is active',
                'const' => true,
            ]);
            expect($result)->toEqual([
                'type' => 'boolean',
                'description' => 'Is active',
                'const' => true,
            ]);
        });
    });

    describe('object()', function () {
        test('should create basic object schema', function () {
            $result = Schema::object([
                'name' => Schema::string(),
                'age' => Schema::number(),
            ]);
            expect($result)->toEqual([
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'age' => ['type' => 'number'],
                ],
                'required' => ['name', 'age'],
                'additionalProperties' => false,
            ]);
        });

        test('should accept additional properties', function () {
            $result = Schema::object(
                [
                    'name' => Schema::string(),
                ],
                [
                    'description' => 'A person object',
                    'title' => 'Person',
                    'additionalProperties' => true,
                ]
            );
            expect($result)->toEqual([
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                ],
                'required' => ['name'],
                'additionalProperties' => true,
                'description' => 'A person object',
                'title' => 'Person',
            ]);
        });

        test('should handle nested objects', function () {
            $result = Schema::object([
                'user' => Schema::object([
                    'name' => Schema::string(),
                    'email' => Schema::string(['format' => 'email']),
                ]),
                'metadata' => Schema::object([
                    'createdAt' => Schema::string(['format' => 'date-time']),
                ]),
            ]);
            expect($result)->toEqual([
                'type' => 'object',
                'properties' => [
                    'user' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'email' => ['type' => 'string', 'format' => 'email'],
                        ],
                        'required' => ['name', 'email'],
                        'additionalProperties' => false,
                    ],
                    'metadata' => [
                        'type' => 'object',
                        'properties' => [
                            'createdAt' => ['type' => 'string', 'format' => 'date-time'],
                        ],
                        'required' => ['createdAt'],
                        'additionalProperties' => false,
                    ],
                ],
                'required' => ['user', 'metadata'],
                'additionalProperties' => false,
            ]);
        });

        test('should handle $defs', function () {
            $personSchema = Schema::object([
                'name' => Schema::string(),
                'age' => Schema::number(),
            ]);

            $result = Schema::object(
                [
                    'leader' => Schema::ref('person'),
                ],
                [
                    '$defs' => ['person' => $personSchema],
                ]
            );

            expect($result)->toEqual([
                'type' => 'object',
                'properties' => [
                    'leader' => ['$ref' => '#/$defs/person'],
                ],
                'required' => ['leader'],
                'additionalProperties' => false,
                '$defs' => [
                    'person' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'age' => ['type' => 'number'],
                        ],
                        'required' => ['name', 'age'],
                        'additionalProperties' => false,
                    ],
                ],
            ]);
        });
    });

    describe('array()', function () {
        test('should create basic array schema', function () {
            $result = Schema::array(Schema::string());
            expect($result)->toEqual([
                'type' => 'array',
                'items' => ['type' => 'string'],
            ]);
        });

        test('should accept additional properties', function () {
            $result = Schema::array(Schema::number(), [
                'description' => 'List of prices',
                'minItems' => 1,
                'maxItems' => 10,
            ]);
            expect($result)->toEqual([
                'type' => 'array',
                'items' => ['type' => 'number'],
                'description' => 'List of prices',
                'minItems' => 1,
                'maxItems' => 10,
            ]);
        });

        test('should handle nested arrays', function () {
            $result = Schema::array(Schema::array(Schema::string()));
            expect($result)->toEqual([
                'type' => 'array',
                'items' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ]);
        });

        test('should handle arrays of objects', function () {
            $result = Schema::array(
                Schema::object([
                    'id' => Schema::integer(),
                    'name' => Schema::string(),
                ])
            );
            expect($result)->toEqual([
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                    ],
                    'required' => ['id', 'name'],
                    'additionalProperties' => false,
                ],
            ]);
        });
    });

    describe('ref()', function () {
        test('should create reference schema', function () {
            $result = Schema::ref('person');
            expect($result)->toEqual([
                '$ref' => '#/$defs/person',
            ]);
        });

        test('should handle different reference names', function () {
            expect(Schema::ref('user'))->toEqual(['$ref' => '#/$defs/user']);
            expect(Schema::ref('address'))->toEqual(['$ref' => '#/$defs/address']);
            expect(Schema::ref('nested-schema'))->toEqual(['$ref' => '#/$defs/nested-schema']);
        });
    });

    describe('anyOf()', function () {
        test('should create basic anyOf schema', function () {
            $result = Schema::anyOf([
                Schema::object(['email' => Schema::string()]),
                Schema::object(['phone' => Schema::string()])
            ]);
            expect($result)->toEqual([
                'anyOf' => [
                    [
                        'type' => 'object',
                        'properties' => ['email' => ['type' => 'string']],
                        'required' => ['email'],
                        'additionalProperties' => false,
                    ],
                    [
                        'type' => 'object',
                        'properties' => ['phone' => ['type' => 'string']],
                        'required' => ['phone'],
                        'additionalProperties' => false,
                    ],
                ],
            ]);
        });

        test('should handle references in anyOf', function () {
            $result = Schema::anyOf([Schema::ref('person'), Schema::ref('company')]);
            expect($result)->toEqual([
                'anyOf' => [
                    ['$ref' => '#/$defs/person'],
                    ['$ref' => '#/$defs/company'],
                ],
            ]);
        });

        test('should handle null in anyOf', function () {
            $result = Schema::anyOf([
                Schema::object(['name' => Schema::string()]),
                ['type' => 'null']
            ]);
            expect($result)->toEqual([
                'anyOf' => [
                    [
                        'type' => 'object',
                        'properties' => ['name' => ['type' => 'string']],
                        'required' => ['name'],
                        'additionalProperties' => false,
                    ],
                    ['type' => 'null'],
                ],
            ]);
        });
    });

    describe('nullable()', function () {
        test('should make string nullable', function () {
            $result = Schema::nullable(Schema::string());
            expect($result)->toEqual([
                'anyOf' => [['type' => 'string'], ['type' => 'null']],
            ]);
        });

        test('should make number nullable', function () {
            $result = Schema::nullable(Schema::number(['minimum' => 0]));
            expect($result)->toEqual([
                'anyOf' => [['type' => 'number', 'minimum' => 0], ['type' => 'null']],
            ]);
        });

        test('should make integer nullable', function () {
            $result = Schema::nullable(Schema::integer());
            expect($result)->toEqual([
                'anyOf' => [['type' => 'integer'], ['type' => 'null']],
            ]);
        });

        test('should make boolean nullable', function () {
            $result = Schema::nullable(Schema::boolean());
            expect($result)->toEqual([
                'anyOf' => [['type' => 'boolean'], ['type' => 'null']],
            ]);
        });

        test('should make object nullable', function () {
            $result = Schema::nullable(Schema::object(['name' => Schema::string()]));
            expect($result)->toEqual([
                'anyOf' => [
                    [
                        'type' => 'object',
                        'properties' => ['name' => ['type' => 'string']],
                        'required' => ['name'],
                        'additionalProperties' => false,
                    ],
                    ['type' => 'null'],
                ],
            ]);
        });

        test('should make array nullable', function () {
            $result = Schema::nullable(Schema::array(Schema::string()));
            expect($result)->toEqual([
                'anyOf' => [
                    ['type' => 'array', 'items' => ['type' => 'string']],
                    ['type' => 'null'],
                ],
            ]);
        });

        test('should handle anyOf schemas', function () {
            $anyOfSchema = Schema::anyOf([
                Schema::object(['email' => Schema::string()]),
                Schema::object(['phone' => Schema::string()])
            ]);
            $result = Schema::nullable($anyOfSchema);
            expect($result)->toEqual([
                'anyOf' => [
                    [
                        'type' => 'object',
                        'properties' => ['email' => ['type' => 'string']],
                        'required' => ['email'],
                        'additionalProperties' => false,
                    ],
                    [
                        'type' => 'object',
                        'properties' => ['phone' => ['type' => 'string']],
                        'required' => ['phone'],
                        'additionalProperties' => false,
                    ],
                    ['type' => 'null'],
                ],
            ]);
        });

        test('should handle reference schemas', function () {
            $refSchema = Schema::ref('person');
            $result = Schema::nullable($refSchema);
            expect($result)->toEqual([
                'anyOf' => [
                    ['$ref' => '#/$defs/person'],
                    ['type' => 'null'],
                ],
            ]);
        });
    });

    describe('Full example schemas', function () {
        test('should create a product schema', function () {
            $result = Schema::object([
                'name' => Schema::string(),
                'price' => Schema::number(['description' => 'Price in dollars']),
                'discount' => Schema::nullable(Schema::number()),
                'tags' => Schema::array(Schema::string()),
                'dimensions' => Schema::object([
                    'width' => Schema::number(),
                    'height' => Schema::number(),
                ]),
            ]);

            expect($result)->toEqual([
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'price' => ['type' => 'number', 'description' => 'Price in dollars'],
                    'discount' => ['anyOf' => [['type' => 'number'], ['type' => 'null']]],
                    'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'dimensions' => [
                        'type' => 'object',
                        'properties' => [
                            'width' => ['type' => 'number'],
                            'height' => ['type' => 'number'],
                        ],
                        'required' => ['width', 'height'],
                        'additionalProperties' => false,
                    ],
                ],
                'required' => ['name', 'price', 'discount', 'tags', 'dimensions'],
                'additionalProperties' => false,
            ]);
        });

        test('should create a schema with references', function () {
            $person = Schema::object([
                'name' => Schema::string(),
                'age' => Schema::number(),
            ]);

            $team = Schema::object(
                [
                    'leader' => Schema::ref('person'),
                    'members' => Schema::array(Schema::ref('person')),
                ],
                [
                    '$defs' => ['person' => $person],
                ]
            );

            expect($team)->toEqual([
                'type' => 'object',
                'properties' => [
                    'leader' => ['$ref' => '#/$defs/person'],
                    'members' => ['type' => 'array', 'items' => ['$ref' => '#/$defs/person']],
                ],
                'required' => ['leader', 'members'],
                'additionalProperties' => false,
                '$defs' => [
                    'person' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'age' => ['type' => 'number'],
                        ],
                        'required' => ['name', 'age'],
                        'additionalProperties' => false,
                    ],
                ],
            ]);
        });

        test('should create a union schema', function () {
            $contactInfo = Schema::anyOf([
                Schema::object(['email' => Schema::string()]),
                Schema::object(['phone' => Schema::string()])
            ]);

            expect($contactInfo)->toEqual([
                'anyOf' => [
                    [
                        'type' => 'object',
                        'properties' => ['email' => ['type' => 'string']],
                        'required' => ['email'],
                        'additionalProperties' => false,
                    ],
                    [
                        'type' => 'object',
                        'properties' => ['phone' => ['type' => 'string']],
                        'required' => ['phone'],
                        'additionalProperties' => false,
                    ],
                ],
            ]);
        });
    });
});

