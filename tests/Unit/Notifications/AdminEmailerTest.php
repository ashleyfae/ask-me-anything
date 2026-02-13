<?php
/**
 * AdminEmailerTest.php
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2026, Ashley Gibson
 * @license   MIT
 */

namespace AskMeAnything\Tests\Unit\Notifications;

use AskMeAnything\Tests\TestCase;
use Exception;
use Generator;
use WP_Mock;

/**
 * @coversDefaultClass \AskMeAnything\Notifications\AdminEmailer
 */
class AdminEmailerTest extends TestCase
{
    /**
     * @covers ::getEmails
     * @dataProvider providerCanGetEmails
     * @throws Exception
     */
    public function testCanGetEmails(
        $adminEmailString,
        array $expectedEmails
    ) : void
    {
        WP_Mock::userFunction('ask_me_anything_get_option')
            ->with('admin_email')
            ->andReturn($adminEmailString);

        WP_Mock::userFunction('is_email')
            ->andReturnTrue();

        $this->assertSame($expectedEmails, $this->invokeInaccessibleMethod(new \AskMeAnything\Notifications\AdminEmailer(), 'getEmails'));
    }

    /** @see testCanGetEmails */
    public function providerCanGetEmails() : Generator
    {
        yield 'false emails' => [
            'adminEmailString' => false,
            'expectedEmails' => [],
        ];

        yield 'empty emails' => [
            'adminEmailString' => '',
            'expectedEmails' => [],
        ];

        yield 'single email' => [
            'adminEmailString' => 'test@example.com',
            'expectedEmails' => ['test@example.com'],
        ];

        yield 'single email with spaces' => [
            'adminEmailString' => ' test@example.com ',
            'expectedEmails' => ['test@example.com'],
        ];

        yield 'multiple emails' => [
            'adminEmailString' => 'first@example.com,second@example.com',
            'expectedEmails' => ['first@example.com', 'second@example.com'],
        ];

        yield 'multiple emails with space between' => [
            'adminEmailString' => 'first@example.com, second@example.com',
            'expectedEmails' => ['first@example.com', 'second@example.com'],
        ];
    }
}
