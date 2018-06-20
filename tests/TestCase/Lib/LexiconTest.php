<?php

namespace Unimatrix\Cake\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;
use Unimatrix\Cake\Lib\Lexicon;

class LexiconTest extends TestCase
{
    public function testMatch() {
        $this->assertTrue(Lexicon::match('tacamul', 'tot tacâmul'));
        $this->assertTrue(Lexicon::match('inca o ora', 'încă o oră'));
        $this->assertTrue(Lexicon::match('mere si prune', 'ana are mere și prune'));
        $this->assertTrue(Lexicon::match('în situația de față poți și să șchiopătezi', 'în situația de față poți și să șchiopătezi'));
    }

    public function testHighlight() {
        $this->assertSame('un test', Lexicon::highlight('un test', ''));
        $this->assertSame('<span class="highlight">îmbunătățit</span>', Lexicon::highlight('îmbunătățit', 'îmbunătățit'));
        $this->assertSame('<span class="highlight">dușmanul</span> a venit', Lexicon::highlight('dușmanul a venit', 'dusmanul'));
        $this->assertSame('cumva ai gasit <span class="highlight">adevărul</span>', Lexicon::highlight('cumva ai gasit adevărul', 'adevarul'));
        $this->assertSame('o frază cu <span class="highlight">câteva cuvinte</span> în ea', Lexicon::highlight('o frază cu câteva cuvinte în ea', 'cateva cuvinte'));
        $this->assertSame('<a href="http://www.google.com"><span class="highlight">google.com</span></a>', Lexicon::highlight('<a href="http://www.google.com">google.com</a>', 'google.com', true));
    }

    public function testCutText() {
        $this->assertSame('a test string', Lexicon::cuttext('a test string'));
        $this->assertSame('hey so this should be trimmed...', Lexicon::cuttext('hey so this should be trimmed down to 30 characters', 30));
        $this->assertSame('...to 30 characters', Lexicon::cuttext(['hey so this should be trimmed down to 30 characters', 'characters'], 30));
        $this->assertSame('...be trimmed...', Lexicon::cuttext(['hey so this should be trimmed down to 20 characters', 'trimmed'], 20));
        $this->assertSame('hey so this should', Lexicon::cuttext('hey so this should be trimmed down to 20 characters', 20, false));
        $this->assertSame('hey so this should|', Lexicon::cuttext('hey so this should be trimmed down to 20 characters', 20, '|'));
    }
}
