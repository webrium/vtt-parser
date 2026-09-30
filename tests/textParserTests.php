<?php
// require_once __DIR__ . '/config.php';

use PHPUnit\Framework\TestCase;

use Vtt\TextParser;

class textParserTests extends TestCase
{

    public function testTextValidation(){


        $vtt = new TextParser;
        $vtt->openFile(__DIR__, 'test.vtt');
        $check = $vtt->textValidation();
        $this->assertTrue($check['ok']);


        $vtt = new TextParser;
        $vtt->openFile(__DIR__, 'incorect.vtt');
        $check = $vtt->textValidation();
        $this->assertFalse($check['ok']);
    }

    public function testTextParse(){
        $vtt = new TextParser;
        $vtt->openFile(__DIR__, 'test.vtt');
        $lines = $vtt->parse();

        $this->assertEquals($lines[count($lines)-1]['text'][0], 'FRANK!!!');
    }

    /**
     * WebVTT allows the hours component to be dropped when it is zero
     * (e.g. "59:40.604" instead of "00:59:40.604"). A cue where both the
     * start and end timestamps use this short form must parse cleanly.
     */
    public function testShortFormTimestampsBelowOneHour(){
        $vtt = new TextParser;
        $vtt->openFile(__DIR__, 'hour-boundary.vtt');
        $lines = $vtt->parse();

        $this->assertSame('00:59:19', $lines[2]['time']['start']);
        $this->assertSame('00:59:40', $lines[2]['time']['end']);
    }

    /**
     * Regression test for the exact bug report: a cue straddling the
     * one-hour mark whose start timestamp omits the hours component while
     * the end timestamp includes it ("59:40.604 --> 01:00:00.940"). This
     * previously threw:
     *   InvalidArgumentException: End time not found in timestamp string(...)
     * because the old regex only matched the full hh:mm:ss.mmm form, so it
     * found just one timestamp (the end) instead of two.
     */
    public function testMixedShortAndFullTimestampAtHourBoundaryDoesNotThrow(){
        $vtt = new TextParser;
        $vtt->openFile(__DIR__, 'hour-boundary.vtt');
        $lines = $vtt->parse();

        $this->assertSame('00:59:40', $lines[3]['time']['start']);
        $this->assertSame('01:00:00', $lines[3]['time']['end']);
    }

    public function testFullFormTimestampsAfterOneHour(){
        $vtt = new TextParser;
        $vtt->openFile(__DIR__, 'hour-boundary.vtt');
        $lines = $vtt->parse();

        $this->assertSame('01:00:00', $lines[4]['time']['start']);
        $this->assertSame('01:00:11', $lines[4]['time']['end']);
    }

    public function testHourBoundaryFileHasNoValidationErrors(){
        $vtt = new TextParser;
        $vtt->openFile(__DIR__, 'hour-boundary.vtt');
        $check = $vtt->textValidation();

        $this->assertTrue($check['ok']);
    }

}




