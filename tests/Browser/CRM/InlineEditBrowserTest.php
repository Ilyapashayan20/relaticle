<?php

declare(strict_types=1);

use App\Enums\CustomFields\CompanyField;
use App\Enums\CustomFields\PeopleField;
use App\Filament\Concerns\EditsRecordFieldsInline;
use App\Filament\Resources\CompanyResource\Pages\ViewCompany;
use App\Filament\Resources\OpportunityResource\Pages\ViewOpportunity;
use App\Filament\Resources\PeopleResource\Pages\ViewPeople;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Opportunity;
use App\Models\People;
use App\Models\User;

mutates(EditsRecordFieldsInline::class, ViewCompany::class, ViewOpportunity::class, ViewPeople::class);

it('toggles company icp from the record view', function (): void {
    $user = User::factory()->withWorkspace()->create();
    $workspace = $user->ownedWorkspaces()->first();
    $company = Company::factory()->recycle([$user, $workspace])->create([
        'name' => 'Northwind',
    ]);
    $icp = CustomField::query()
        ->forEntity(Company::class)
        ->where('code', CompanyField::ICP)
        ->firstOrFail();

    loginViaBrowser($user)
        ->assertPathIs("/app/{$workspace->slug}")
        ->navigate("/app/{$workspace->slug}/companies/{$company->getKey()}")
        ->assertSee('Northwind')
        ->assertVisible('[data-inline-field="icp"] [role="switch"]')
        ->assertAttribute('[data-inline-field="icp"] [role="switch"]', 'aria-checked', 'false')
        ->click('[data-inline-field="icp"] [role="switch"]')
        ->assertAttribute('[data-inline-field="icp"] [role="switch"]', 'aria-checked', 'true')
        ->assertNoJavaScriptErrors();

    expect($company->fresh()->customFieldValues()
        ->where('custom_field_id', $icp->getKey())
        ->value($icp->getValueColumn()))->toBeTrue();
});

it('opens company name click-to-edit and saves from the keyboard', function (): void {
    $user = User::factory()->withWorkspace()->create();
    $workspace = $user->ownedWorkspaces()->first();
    $company = Company::factory()->recycle([$user, $workspace])->create([
        'name' => 'Northwind',
    ]);

    $page = loginViaBrowser($user)
        ->assertPathIs("/app/{$workspace->slug}")
        ->navigate("/app/{$workspace->slug}/companies/{$company->getKey()}")
        ->assertSee('Northwind')
        ->click('[data-inline-field="name"] .fi-in-entry-content')
        ->assertVisible('[data-inline-field="name"] .fi-inline-field-editor input.fi-input')
        ->assertNoJavaScriptErrors();

    $page->keys('[data-inline-field="name"] .fi-inline-field-editor input.fi-input', ['Control+a'])
        ->type('[data-inline-field="name"] .fi-inline-field-editor input.fi-input', 'Contoso')
        ->keys('[data-inline-field="name"] .fi-inline-field-editor input.fi-input', 'Enter')
        ->assertSee('Contoso')
        ->assertNoJavaScriptErrors();

    expect($company->fresh()->name)->toBe('Contoso');
});

it('stacks the opportunity name above company and contact chips', function (): void {
    $user = User::factory()->withWorkspace()->create();
    $workspace = $user->ownedWorkspaces()->first();
    $company = Company::factory()->recycle([$user, $workspace])->create([
        'name' => 'Acme',
    ]);
    $contact = People::factory()->recycle([$user, $workspace])->create([
        'name' => 'Ada Lovelace',
        'company_id' => $company->getKey(),
    ]);
    $opportunity = Opportunity::factory()->recycle([$user, $workspace])->create([
        'name' => 'Enterprise rollout',
        'company_id' => $company->getKey(),
        'contact_id' => $contact->getKey(),
    ]);

    $page = loginViaBrowser($user)
        ->assertPathIs("/app/{$workspace->slug}")
        ->resize(1440, 900)
        ->navigate("/app/{$workspace->slug}/opportunities/{$opportunity->getKey()}")
        ->assertSee('Enterprise rollout')
        ->assertSee('Acme')
        ->assertSee('Ada Lovelace')
        ->assertNoJavaScriptErrors();

    $layout = $page->script(<<<'JS'
        (() => {
            const name = document.querySelector('[data-inline-field="name"]').getBoundingClientRect();
            const company = document.querySelector('[data-inline-field="company_id"]').getBoundingClientRect();
            const contact = document.querySelector('[data-inline-field="contact_id"]').getBoundingClientRect();

            return {
                companyBelowName: company.top >= name.bottom - 1,
                contactBelowName: contact.top >= name.bottom - 1,
                chipsOverlapName: company.top < name.bottom - 4 && company.left < name.right - 4,
            };
        })();
    JS);

    expect($layout)->toMatchArray([
        'companyBelowName' => true,
        'contactBelowName' => true,
        'chipsOverlapName' => false,
    ]);
});

it('stacks company fields in one column on the desktop record view', function (): void {
    $user = User::factory()->withWorkspace()->create();
    $workspace = $user->ownedWorkspaces()->first();
    $company = Company::factory()->recycle([$user, $workspace])->create([
        'name' => 'Northwind',
        'account_owner_id' => $user->id,
    ]);

    $page = loginViaBrowser($user)
        ->assertPathIs("/app/{$workspace->slug}")
        ->resize(1440, 900)
        ->navigate("/app/{$workspace->slug}/companies/{$company->getKey()}")
        ->assertSee('Northwind')
        ->assertVisible('[data-inline-field="account_owner_id"]')
        ->assertVisible('[data-inline-field="icp"]')
        ->assertNoJavaScriptErrors();

    $layout = $page->script(<<<'JS'
        (() => {
            const owner = document.querySelector('[data-inline-field="account_owner_id"]').getBoundingClientRect();
            const icp = document.querySelector('[data-inline-field="icp"]').getBoundingClientRect();
            const domains = document.querySelector('[data-inline-field="domains"]')?.getBoundingClientRect();

            return {
                icpBelowOwner: icp.top >= owner.bottom - 1,
                icpNotBesideOwner: icp.left < owner.right - 24 || icp.top >= owner.bottom - 1,
                domainsBelowIcp: domains ? domains.top >= icp.bottom - 1 : true,
            };
        })();
    JS);

    expect($layout)->toMatchArray([
        'icpBelowOwner' => true,
        'icpNotBesideOwner' => true,
        'domainsBelowIcp' => true,
    ]);
});

it('opens the person email editor from the extra-count control', function (): void {
    $user = User::factory()->withWorkspace()->create();
    $workspace = $user->ownedWorkspaces()->first();
    $person = People::factory()->recycle([$user, $workspace])->create([
        'name' => 'Ilya Pashayan',
    ]);
    $emails = CustomField::query()
        ->forEntity(People::class)
        ->where('code', PeopleField::EMAILS)
        ->firstOrFail();
    $person->saveCustomFieldValue($emails, [
        'first@example.test',
        'second@example.test',
        'third@example.test',
    ]);

    loginViaBrowser($user)
        ->assertPathIs("/app/{$workspace->slug}")
        ->resize(1440, 900)
        ->navigate("/app/{$workspace->slug}/people/{$person->getKey()}")
        ->assertSee('first@example.test')
        ->assertSee(__('filament/inline-edit.show_n_more', ['count' => 2]))
        ->click('[data-inline-field="emails"] .fi-multi-value-more')
        ->assertVisible('[data-inline-field="emails"] .fi-inline-field-editor')
        ->assertAttribute('[data-inline-field="emails"]', 'data-inline-editing', 'true')
        ->assertNoJavaScriptErrors();
});

it('opens the company from the chip and edits from the rest of the field', function (): void {
    $user = User::factory()->withWorkspace()->create();
    $workspace = $user->ownedWorkspaces()->first();
    $company = Company::factory()->recycle([$user, $workspace])->create([
        'name' => 'Escrow',
    ]);
    $person = People::factory()->recycle([$user, $workspace])->create([
        'name' => 'Ilya Pashayan',
        'company_id' => $company->getKey(),
    ]);

    $personUrl = "/app/{$workspace->slug}/people/{$person->getKey()}";
    $companyUrl = "/app/{$workspace->slug}/companies/{$company->getKey()}";

    $page = loginViaBrowser($user)
        ->assertPathIs("/app/{$workspace->slug}")
        ->resize(1440, 900)
        ->navigate($personUrl)
        ->assertSee('Escrow')
        ->click('[data-inline-field="company_id"] .fi-in-entry-label')
        ->assertVisible('[data-inline-field="company_id"] .fi-inline-field-editor')
        ->assertPathIs($personUrl)
        ->assertNoJavaScriptErrors();

    $page->navigate($personUrl)
        ->click('[data-inline-field="company_id"] a.fi-record-chip')
        ->assertPathIs($companyUrl)
        ->assertNoJavaScriptErrors();
});
