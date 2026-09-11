<?php

declare(strict_types=1);

use App\Livewire\App\AccessTokens\CreateAccessToken;
use App\Models\User;

mutates(CreateAccessToken::class);

it('copies a new access token when the browser has no clipboard api', function (): void {
    $user = User::factory()->withTeam()->create();
    $team = $user->ownedTeams()->first();

    $page = loginViaBrowser($user)
        ->assertPathIs("/app/{$team->slug}")
        ->navigate("/app/{$team->slug}/settings/access-tokens")
        ->type('[id="form.name"]', 'Deploy script')
        ->select('[id="form.expiration"]', '30')
        ->click('form[wire\\:submit="createToken"] button[type="submit"]')
        ->waitForText('Please copy your new access token');

    $page->script(<<<'JS'
        (() => {
            Object.defineProperty(navigator, 'clipboard', { value: undefined })

            document.execCommand = () => {
                const field = document.activeElement

                window.copiedToken = field.value.slice(field.selectionStart, field.selectionEnd)

                return true
            }
        })()
        JS);

    $page->click('[aria-label="Copy to clipboard"]')
        ->waitForText('Copied!');

    expect(hash('sha256', $page->script('window.copiedToken')))
        ->toBe($user->tokens()->sole()->token);
});
