<?php

declare(strict_types=1);

use App\Livewire\App\AccessTokens\CreateAccessToken;
use App\Models\User;
use Pest\Browser\Api\AwaitableWebpage;

mutates(CreateAccessToken::class);

function showNewAccessToken(User $user): AwaitableWebpage
{
    $team = $user->ownedTeams()->first();

    return loginViaBrowser($user)
        ->assertPathIs("/app/{$team->slug}")
        ->navigate("/app/{$team->slug}/settings/access-tokens")
        ->type('[id="form.name"]', 'Deploy script')
        ->select('[id="form.expiration"]', '30')
        ->click('form[wire\\:submit="createToken"] button[type="submit"]')
        ->waitForText('Please copy your new access token');
}

it('copies a new access token when the browser has no clipboard api', function (): void {
    $user = User::factory()->withTeam()->create();

    $page = showNewAccessToken($user);

    removeClipboardApi($page);

    $page->click('[aria-label="Copy to clipboard"]')
        ->waitForText('Copied!');

    expect(hash('sha256', $page->script('window.copiedText')))
        ->toBe($user->tokens()->sole()->token);
});

it('holds the copied confirmation for two seconds after the latest click', function (): void {
    $page = showNewAccessToken(User::factory()->withTeam()->create());

    removeClipboardApi($page);

    $page->click('[aria-label="Copy to clipboard"]')
        ->wait(1.5)
        ->click('[aria-label="Copy to clipboard"]')
        ->wait(1.3);

    expect($page->script('Alpine.$data(document.querySelector(\'[aria-label="Copy to clipboard"]\')).copied'))
        ->toBeTrue();
});

it('gives the copy button no native title tooltip', function (): void {
    showNewAccessToken(User::factory()->withTeam()->create())
        ->assertAttributeMissing('[aria-label="Copy to clipboard"]', 'title');
});
