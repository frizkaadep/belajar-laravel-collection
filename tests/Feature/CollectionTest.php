<?php

namespace Tests\Feature;

use App\Data\Person;
use Faker\Guesser\Name;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\LazyCollection;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertEqualsCanonicalizing;

class CollectionTest extends TestCase
{
    public function testCollection()
    {
        $collection = collect([1, 2, 3]);
        $this->assertEqualsCanonicalizing([1, 2, 3], $collection->all());
    }

    public function testForEach()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        foreach ($collection as $key => $value) {
            $this->assertEquals(expected: $key + 1, actual: $value);
        }
    }

    public function testCrud()
    {
        $collection = collect([]);
        $collection->push(1, 2, 3);
        $this->assertEqualsCanonicalizing([1, 2, 3], $collection->all());

        $result = $collection->pop();
        $this->assertEquals(3, $result);
        $this->assertEqualsCanonicalizing([1, 2], $collection->all());
    }

    public function testMap()
    {
        $collection = collect([1, 2, 3]);
        $result = $collection->map(function ($item) {
            return $item * 2;
        });
        $this->assertEqualsCanonicalizing([2, 4, 6], $result->all());
    }

    public function testMapInto()
    {
        $collection = collect(["Ade"]);
        $result = $collection->mapInto(Person::class);
        $this->assertEquals([new Person("Ade")], $result->all());
    }

    public function testMapSpread()
    {
        $collection = collect([
            ["Ashar", "Yahya"],
            ["Frizka", "Ade"]
        ]);

        $result = $collection->mapSpread(function ($firstname, $lastname) {
            $fullName = $firstname . ' ' . $lastname;
            return new Person($fullName);
        });

        $this->assertEquals([
            new Person("Ashar Yahya"),
            new Person("Frizka Ade"),
        ], $result->all());
    }

    public function testMapToGroups()
    {
        $collection = collect([
            [
                "name" => "Frizka",
                "department" => "IT"
            ],
            [
                "name" => "Ade",
                "department" => "IT"
            ],
            [
                "name" => "Budi",
                "department" => "HR"
            ]
        ]);

        $result = $collection->mapToGroups(function ($person) {
            return [
                $person["department"] => $person["name"]
            ];
        });

        $this->assertEquals([
            "IT" => collect(["Frizka", "Ade"]),
            "HR" => collect(["Budi"]),
        ], $result->all());
    }

    public function testZip()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->zip($collection2);

        assertEquals([
            collect([1, 4]),
            collect([2, 5]),
            collect([3, 6])
        ], $collection3->all());
    }

    public function testConcat()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->concat($collection2);

        assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6], $collection3->all());
    }

    public function testCombine()
    {
        $collection1 = ["name", "country"];
        $collection2 = ["Frizka", "Indonesia"];
        $collection3 = collect($collection1)->combine($collection2);

        assertEqualsCanonicalizing([
            "name" => "Frizka",
            "country" => "Indonesia"
        ], $collection3->all());
    }

    public function testCollapse()
    {
        $collection = collect([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ]);
        $result = $collection->collapse();
        assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());
    }

    public function testFlatMap()
    {
        $collection = collect([
            [
                "name" => "Frizka",
                "hobbies" => ["Coding", "Gaming"]
            ],
            [
                "name" => "Ade",
                "hobbies" => ["Reading", "Writing"]
            ]
        ]);

        $result = $collection->flatMap(function ($item) {
            $hobbies = $item["hobbies"];
            return $hobbies;
        });
        $this->assertEqualsCanonicalizing(["Reading", "Writing", "Coding", "Gaming"], $result->all());
    }

    public function testJoin()
    {
        $collection = collect(["Frizka", "Ade", "Maulana"]);

        assertEqualsCanonicalizing("Frizka-Ade-Maulana", $collection->join("-"));
        assertEqualsCanonicalizing("Frizka-Ade_Maulana", $collection->join("-", "_"));
    }

    public function testFilter()
    {
        $collection = collect([
            "Frizka" => 100,
            "Ade" => 80,
            "Budi" => 90
        ]);
        $result = $collection->filter(function ($item, $key) {
            return $item >= 90;
        });
        $this->assertEqualsCanonicalizing([
            "Frizka" => 100,
            "Budi" => 90
        ], $result->all());
    }

    public function testFilterIndex()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collection->filter(function ($value, $key) {
            return $value % 2 == 0;
        });

        $this->assertEqualsCanonicalizing([2, 4, 6, 8, 10], $result->all());
    }

    public function testPartition()
    {
        $collection = collect([
            "Frizka" => 100,
            "Ade" => 80,
            "Budi" => 90
        ]);
        [$result1, $result2] = $collection->partition(function ($item, $key) {
            return $item >= 90;
        });
        assertEquals(["Frizka" => 100, "Budi" => 90], $result1->all());
        assertEquals(["Ade" => 80], $result2->all());
    }

    public function testTesting()
    {
        $collection = collect(["Frizka", "Ade", "Prasurya"]);
        $this->assertTrue($collection->contains("Frizka"));
        $this->assertTrue($collection->contains(function ($value, $key) {
            return $value == "Prasurya";
        }));
    }

    public function testGrouping()
    {
        $collection = collect([
            [
                "name" => "Ade",
                "department" => "IT"
            ],
            [
                "name" => "Frizka",
                "department" => "IT"
            ],
            [
                "name" => "Yahya",
                "department" => "HR"
            ]
        ]);

        $result = $collection->groupBy("department");

        assertEquals([
            "IT" => collect([
                [
                    "name" => "Ade",
                    "department" => "IT"
                ],
                [
                    "name" => "Frizka",
                    "department" => "IT"
                ]
            ]),
            "HR" => collect([
                [
                    "name" => "Yahya",
                    "department" => "HR"
                ]
            ])
        ], $result->all());


        $result = $collection->groupBy(function ($value, $key) {
            return strtolower($value["department"]);
        });

        assertEquals([
            "it" => collect([
                [
                    "name" => "Ade",
                    "department" => "IT"
                ],
                [
                    "name" => "Frizka",
                    "department" => "IT"
                ]
            ]),
            "hr" => collect([
                [
                    "name" => "Yahya",
                    "department" => "HR"
                ]
            ])
        ], $result->all());
    }

    public function testSlice()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->slice(3);
        assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->slice(3, 2);
        assertEqualsCanonicalizing([4, 5], $result->all());
    }

    public function testTake()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->take(3);
        assertEqualsCanonicalizing([1, 2, 3], $result->all());

        $result = $collection->takeUntil(function ($value, $key) {
            return $value == 3;
        });
        assertEqualsCanonicalizing([1, 2], $result->all());

        $result = $collection->takeWhile(function ($value, $key) {
            return $value < 3;
        });
        assertEqualsCanonicalizing([1, 2], $result->all());
    }

    public function testSkip()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->skip(3);
        assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipUntil(function ($value, $key) {
            return $value == 3;
        });
        assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipWhile(function ($value, $key) {
            return $value < 3;
        });
        assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());
    }

    public function testChunked()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collection->chunk(3);

        assertEqualsCanonicalizing([1, 2, 3], $result->all()[0]->all());
        assertEqualsCanonicalizing([4, 5, 6], $result->all()[1]->all());
        assertEqualsCanonicalizing([7, 8, 9], $result->all()[2]->all());
        assertEqualsCanonicalizing([10], $result->all()[3]->all());
    }

    public function testFirst()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->first();
        assertEquals(1, $result);

        $result = $collection->first(function ($value, $key) {
            return $value > 5;
        });
        assertEquals(6, $result);
    }

    public function testLast()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->last();
        assertEquals(9, $result);

        $result = $collection->last(function ($value, $key) {
            return $value < 5;
        });
        assertEquals(4, $result);
    }

    public function testRandom()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->random();
        self::assertTrue(in_array($result, [1, 2, 3, 4, 5, 6, 7, 8, 9]));
    }

    public function testCheckingExistence()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        self::assertTrue($collection->isNotEmpty());
        self::assertFalse($collection->isEmpty());
        self::assertTrue($collection->contains(8));
        self::assertTrue($collection->contains(function ($value, $key) {
            return $value == 8;
        }));
    }

    public function testOrdering()
    {
        $collection = collect([1, 3, 2, 4, 5, 7, 6, 8, 9]);
        $result = $collection->sort();
        assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->sortDesc();
        assertEqualsCanonicalizing([9, 8, 7, 6, 5, 4, 3, 2, 1], $result->all());
    }

    public function testAggregate()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->sum();
        assertEquals(45, $result);

        $result = $collection->avg();
        assertEquals(5, $result);

        $result = $collection->min();
        assertEquals(1, $result);

        $result = $collection->max();
        assertEquals(9, $result);
    }

    public function testReduce()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->reduce(function ($carry, $item) {
            return $carry + $item;
        });
        assertEquals(45, $result);
    }

    public function testLazyCollection()
    {
        $collection = LazyCollection::make(function () {
            $value = 0;
            while (true) {
                yield $value;
                $value++;
            }
        });

        $result = $collection->take(10);
        assertEqualsCanonicalizing([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());
    }
}
