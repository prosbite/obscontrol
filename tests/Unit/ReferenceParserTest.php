<?php

use App\Services\Bible\ReferenceParseException;
use App\Services\Bible\ReferenceParser;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->parser = new ReferenceParser(config('bible.books'));
});

test('canonicalises a variety of references', function (string $input, array $expected) {
    $references = $this->parser->parse($input);

    expect(array_map(static fn ($reference) => $reference->canonical(), $references))->toBe($expected);
})->with([
    'single verse' => ['John 3:16', ['JHN.3.16']],
    'verse range' => ['John 3:16-18', ['JHN.3.16-JHN.3.18']],
    'whole chapter by full name' => ['Psalm 23', ['PSA.23']],
    'whole chapter by abbreviation' => ['Ps 23', ['PSA.23']],
    'cross chapter range' => ['John 3:16-4:2', ['JHN.3.16-JHN.4.2']],
    'short book alias' => ['Jn 3:16', ['JHN.3.16']],
    'numbered book with space' => ['1 Cor 13:4', ['1CO.13.4']],
    'numbered book no space' => ['1Cor 13:4-7', ['1CO.13.4-1CO.13.7']],
    'multi word book' => ['Song of Solomon 2:1', ['SNG.2.1']],
    'multiple references' => ['John 3:16; Rom 5:8', ['JHN.3.16', 'ROM.5.8']],
    'comma book separator' => ['John 3:16, Rom 5:8', ['JHN.3.16', 'ROM.5.8']],
    'verse list merges contiguous' => ['John 3:16,17', ['JHN.3.16-JHN.3.17']],
    'verse list keeps gaps' => ['John 3:16,18', ['JHN.3.16', 'JHN.3.18']],
    'range then extra verses' => ['John 3:16-18,20-22', ['JHN.3.16-JHN.3.18', 'JHN.3.20-JHN.3.22']],
    'single chapter book verse' => ['Jude 1', ['JUD.1.1']],
    'single chapter explicit' => ['Jude 1:1', ['JUD.1.1']],
    'single chapter whole book' => ['Jude', ['JUD.1']],
    'psalm range' => ['Psalm 23:1-6', ['PSA.23.1-PSA.23.6']],
    'revelation' => ['Revelation 22:21', ['REV.22.21']],
]);

test('rejects malformed references', function (string $input) {
    $this->parser->parse($input);
})->with([
    'empty' => [''],
    'unknown book' => ['Notabook 1:1'],
    'book without number detail' => ['John abc'],
    'bare number' => ['12345'],
    'dangling colon' => ['John 3:'],
])->throws(ReferenceParseException::class);
