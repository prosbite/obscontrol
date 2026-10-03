<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Canonical Bible Book Metadata
|--------------------------------------------------------------------------
|
| Keyed by USFM book id (shared by API.Bible and internal canonical refs).
| "aliases" holds common names/abbreviations used for reference parsing.
| "chapters" is the chapter count, "testament" is OT or NT.
|
*/

return [
    'GEN' => ['name' => 'Genesis', 'aliases' => ['Gen', 'Ge', 'Gn'], 'chapters' => 50, 'testament' => 'OT'],
    'EXO' => ['name' => 'Exodus', 'aliases' => ['Exod', 'Exo', 'Ex', 'Exd'], 'chapters' => 40, 'testament' => 'OT'],
    'LEV' => ['name' => 'Leviticus', 'aliases' => ['Lev', 'Lv', 'Le'], 'chapters' => 27, 'testament' => 'OT'],
    'NUM' => ['name' => 'Numbers', 'aliases' => ['Num', 'Nu', 'Nm', 'Nb'], 'chapters' => 36, 'testament' => 'OT'],
    'DEU' => ['name' => 'Deuteronomy', 'aliases' => ['Deut', 'Deu', 'Dt'], 'chapters' => 34, 'testament' => 'OT'],
    'JOS' => ['name' => 'Joshua', 'aliases' => ['Josh', 'Jos', 'Jsh'], 'chapters' => 24, 'testament' => 'OT'],
    'JDG' => ['name' => 'Judges', 'aliases' => ['Judg', 'Jdg', 'Jg', 'Jdgs'], 'chapters' => 21, 'testament' => 'OT'],
    'RUT' => ['name' => 'Ruth', 'aliases' => ['Rth', 'Ru'], 'chapters' => 4, 'testament' => 'OT'],
    '1SA' => ['name' => '1 Samuel', 'aliases' => ['1 Samuel', '1Samuel', '1 Sam', '1Sam', '1Sa', '1 Sm', '1Sm', 'I Samuel', 'First Samuel'], 'chapters' => 31, 'testament' => 'OT'],
    '2SA' => ['name' => '2 Samuel', 'aliases' => ['2 Samuel', '2Samuel', '2 Sam', '2Sam', '2Sa', '2 Sm', '2Sm', 'II Samuel', 'Second Samuel'], 'chapters' => 24, 'testament' => 'OT'],
    '1KI' => ['name' => '1 Kings', 'aliases' => ['1 Kings', '1Kings', '1 Kgs', '1Kgs', '1 Ki', '1Ki', 'I Kings', 'First Kings'], 'chapters' => 22, 'testament' => 'OT'],
    '2KI' => ['name' => '2 Kings', 'aliases' => ['2 Kings', '2Kings', '2 Kgs', '2Kgs', '2 Ki', '2Ki', 'II Kings', 'Second Kings'], 'chapters' => 25, 'testament' => 'OT'],
    '1CH' => ['name' => '1 Chronicles', 'aliases' => ['1 Chronicles', '1Chronicles', '1 Chron', '1Chron', '1 Chr', '1Chr', '1Ch', 'I Chronicles', 'First Chronicles'], 'chapters' => 29, 'testament' => 'OT'],
    '2CH' => ['name' => '2 Chronicles', 'aliases' => ['2 Chronicles', '2Chronicles', '2 Chron', '2Chron', '2 Chr', '2Chr', '2Ch', 'II Chronicles', 'Second Chronicles'], 'chapters' => 36, 'testament' => 'OT'],
    'EZR' => ['name' => 'Ezra', 'aliases' => ['Ezr'], 'chapters' => 10, 'testament' => 'OT'],
    'NEH' => ['name' => 'Nehemiah', 'aliases' => ['Neh'], 'chapters' => 13, 'testament' => 'OT'],
    'EST' => ['name' => 'Esther', 'aliases' => ['Esth', 'Est'], 'chapters' => 10, 'testament' => 'OT'],
    'JOB' => ['name' => 'Job', 'aliases' => ['Jb'], 'chapters' => 42, 'testament' => 'OT'],
    'PSA' => ['name' => 'Psalms', 'aliases' => ['Psalm', 'Psalms', 'Ps', 'Psa', 'Psm', 'Pss'], 'chapters' => 150, 'testament' => 'OT'],
    'PRO' => ['name' => 'Proverbs', 'aliases' => ['Prov', 'Pro', 'Prv', 'Pr'], 'chapters' => 31, 'testament' => 'OT'],
    'ECC' => ['name' => 'Ecclesiastes', 'aliases' => ['Eccles', 'Eccl', 'Ecc', 'Qoheleth'], 'chapters' => 12, 'testament' => 'OT'],
    'SNG' => ['name' => 'Song of Solomon', 'aliases' => ['Song of Songs', 'Song of Solomon', 'Canticles', 'Song', 'SOS', 'Sng', 'SoS'], 'chapters' => 8, 'testament' => 'OT'],
    'ISA' => ['name' => 'Isaiah', 'aliases' => ['Isa', 'Is'], 'chapters' => 66, 'testament' => 'OT'],
    'JER' => ['name' => 'Jeremiah', 'aliases' => ['Jer', 'Je'], 'chapters' => 52, 'testament' => 'OT'],
    'LAM' => ['name' => 'Lamentations', 'aliases' => ['Lam', 'La'], 'chapters' => 5, 'testament' => 'OT'],
    'EZK' => ['name' => 'Ezekiel', 'aliases' => ['Ezek', 'Eze', 'Ezk'], 'chapters' => 48, 'testament' => 'OT'],
    'DAN' => ['name' => 'Daniel', 'aliases' => ['Dan', 'Dn', 'Da'], 'chapters' => 12, 'testament' => 'OT'],
    'HOS' => ['name' => 'Hosea', 'aliases' => ['Hos', 'Ho'], 'chapters' => 14, 'testament' => 'OT'],
    'JOL' => ['name' => 'Joel', 'aliases' => ['Joe', 'Jl'], 'chapters' => 3, 'testament' => 'OT'],
    'AMO' => ['name' => 'Amos', 'aliases' => ['Am'], 'chapters' => 9, 'testament' => 'OT'],
    'OBA' => ['name' => 'Obadiah', 'aliases' => ['Obad', 'Oba', 'Ob'], 'chapters' => 1, 'testament' => 'OT'],
    'JON' => ['name' => 'Jonah', 'aliases' => ['Jon', 'Jnh'], 'chapters' => 4, 'testament' => 'OT'],
    'MIC' => ['name' => 'Micah', 'aliases' => ['Mic', 'Mi'], 'chapters' => 7, 'testament' => 'OT'],
    'NAM' => ['name' => 'Nahum', 'aliases' => ['Nah', 'Na'], 'chapters' => 3, 'testament' => 'OT'],
    'HAB' => ['name' => 'Habakkuk', 'aliases' => ['Hab', 'Hb'], 'chapters' => 3, 'testament' => 'OT'],
    'ZEP' => ['name' => 'Zephaniah', 'aliases' => ['Zeph', 'Zep', 'Zp'], 'chapters' => 3, 'testament' => 'OT'],
    'HAG' => ['name' => 'Haggai', 'aliases' => ['Hag', 'Hg'], 'chapters' => 2, 'testament' => 'OT'],
    'ZEC' => ['name' => 'Zechariah', 'aliases' => ['Zech', 'Zec', 'Zc'], 'chapters' => 14, 'testament' => 'OT'],
    'MAL' => ['name' => 'Malachi', 'aliases' => ['Mal', 'Ml'], 'chapters' => 4, 'testament' => 'OT'],
    'MAT' => ['name' => 'Matthew', 'aliases' => ['Matt', 'Mat', 'Mt'], 'chapters' => 28, 'testament' => 'NT'],
    'MRK' => ['name' => 'Mark', 'aliases' => ['Mrk', 'Mar', 'Mk', 'Mr'], 'chapters' => 16, 'testament' => 'NT'],
    'LUK' => ['name' => 'Luke', 'aliases' => ['Luk', 'Lk'], 'chapters' => 24, 'testament' => 'NT'],
    'JHN' => ['name' => 'John', 'aliases' => ['Jhn', 'Jn'], 'chapters' => 21, 'testament' => 'NT'],
    'ACT' => ['name' => 'Acts', 'aliases' => ['Act', 'Ac'], 'chapters' => 28, 'testament' => 'NT'],
    'ROM' => ['name' => 'Romans', 'aliases' => ['Rom', 'Ro', 'Rm'], 'chapters' => 16, 'testament' => 'NT'],
    '1CO' => ['name' => '1 Corinthians', 'aliases' => ['1 Corinthians', '1Corinthians', '1 Cor', '1Cor', '1 Co', '1Co', 'I Corinthians', 'First Corinthians'], 'chapters' => 16, 'testament' => 'NT'],
    '2CO' => ['name' => '2 Corinthians', 'aliases' => ['2 Corinthians', '2Corinthians', '2 Cor', '2Cor', '2 Co', '2Co', 'II Corinthians', 'Second Corinthians'], 'chapters' => 13, 'testament' => 'NT'],
    'GAL' => ['name' => 'Galatians', 'aliases' => ['Gal', 'Ga'], 'chapters' => 6, 'testament' => 'NT'],
    'EPH' => ['name' => 'Ephesians', 'aliases' => ['Eph', 'Ep'], 'chapters' => 6, 'testament' => 'NT'],
    'PHP' => ['name' => 'Philippians', 'aliases' => ['Phil', 'Php', 'Pp'], 'chapters' => 4, 'testament' => 'NT'],
    'COL' => ['name' => 'Colossians', 'aliases' => ['Col', 'Co'], 'chapters' => 4, 'testament' => 'NT'],
    '1TH' => ['name' => '1 Thessalonians', 'aliases' => ['1 Thessalonians', '1Thessalonians', '1 Thess', '1Thess', '1 Th', '1Th', 'I Thessalonians', 'First Thessalonians'], 'chapters' => 5, 'testament' => 'NT'],
    '2TH' => ['name' => '2 Thessalonians', 'aliases' => ['2 Thessalonians', '2Thessalonians', '2 Thess', '2Thess', '2 Th', '2Th', 'II Thessalonians', 'Second Thessalonians'], 'chapters' => 3, 'testament' => 'NT'],
    '1TI' => ['name' => '1 Timothy', 'aliases' => ['1 Timothy', '1Timothy', '1 Tim', '1Tim', '1 Ti', '1Ti', 'I Timothy', 'First Timothy'], 'chapters' => 6, 'testament' => 'NT'],
    '2TI' => ['name' => '2 Timothy', 'aliases' => ['2 Timothy', '2Timothy', '2 Tim', '2Tim', '2 Ti', '2Ti', 'II Timothy', 'Second Timothy'], 'chapters' => 4, 'testament' => 'NT'],
    'TIT' => ['name' => 'Titus', 'aliases' => ['Tit', 'Ti'], 'chapters' => 3, 'testament' => 'NT'],
    'PHM' => ['name' => 'Philemon', 'aliases' => ['Philem', 'Phlm', 'Phm'], 'chapters' => 1, 'testament' => 'NT'],
    'HEB' => ['name' => 'Hebrews', 'aliases' => ['Heb', 'He'], 'chapters' => 13, 'testament' => 'NT'],
    'JAS' => ['name' => 'James', 'aliases' => ['Jas', 'Jm'], 'chapters' => 5, 'testament' => 'NT'],
    '1PE' => ['name' => '1 Peter', 'aliases' => ['1 Peter', '1Peter', '1 Pet', '1Pet', '1 Pe', '1Pe', 'I Peter', 'First Peter'], 'chapters' => 5, 'testament' => 'NT'],
    '2PE' => ['name' => '2 Peter', 'aliases' => ['2 Peter', '2Peter', '2 Pet', '2Pet', '2 Pe', '2Pe', 'II Peter', 'Second Peter'], 'chapters' => 3, 'testament' => 'NT'],
    '1JN' => ['name' => '1 John', 'aliases' => ['1 John', '1John', '1 Jn', '1Jn', 'I John', 'First John'], 'chapters' => 5, 'testament' => 'NT'],
    '2JN' => ['name' => '2 John', 'aliases' => ['2 John', '2John', '2 Jn', '2Jn', 'II John', 'Second John'], 'chapters' => 1, 'testament' => 'NT'],
    '3JN' => ['name' => '3 John', 'aliases' => ['3 John', '3John', '3 Jn', '3Jn', 'III John', 'Third John'], 'chapters' => 1, 'testament' => 'NT'],
    'JUD' => ['name' => 'Jude', 'aliases' => ['Jud', 'Jde'], 'chapters' => 1, 'testament' => 'NT'],
    'REV' => ['name' => 'Revelation', 'aliases' => ['Rev', 'Re', 'Rv', 'Apocalypse'], 'chapters' => 22, 'testament' => 'NT'],
];
