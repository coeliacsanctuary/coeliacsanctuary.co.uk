<?php

declare(strict_types=1);

namespace Tests\Code;

use Algolia\ScoutExtended\Searchable\Aggregator;
use App\Contracts\Search\IsSearchable;
use App\Feeds\Feed;
use App\Http\Middleware\HandleInertiaRequests;
use App\Infrastructure\Notification;
use App\Mailables\BaseMailable;
use App\Mailables\Shop\BaseShopMailable;
use App\Models\User;
use App\Pipelines\EatingOut\CheckRecommendedPlace\Steps\AbstractStepAction;
use App\Providers\NovaServiceProvider;
use App\Resources\Shop\ShopTravelCardProductResource;
use App\Support\EatingOut\SuggestEdits\Fields\EditableField;
use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Mail\Mailable;
use Illuminate\Support\ServiceProvider;
use Jpeters8889\PhpUnitCodeAssertions\CodeAssertionsTestCase;
use PHPUnit\Framework\Attributes\Test;

class CodeArchitectureTest extends CodeAssertionsTestCase
{
    #[Test]
    public function allAppCodeDeclaresStrictTypes(): void
    {
        $this->assertCodeIn('app')->usesStrictTypes();
    }

    #[Test]
    public function noCodeUsesDebugFunctionCalls(): void
    {
        $this->assertCodeIn('app')->doesNotUseFunctions(['dd', 'dump']);
    }

    #[Test]
    public function allActionsFollowTheSamePattern(): void
    {
        $this->assertClassesIn('app/Actions')
            ->areClasses()
            ->hasMethod('handle')
            ->hasSuffix('Action');
    }

    #[Test]
    public function allCastablesInCastsImplementTheCastsAttributesInterface(): void
    {
        $this->assertClassesIn('app/Casts')->implement(CastsAttributes::class);
    }

    #[Test]
    public function allFilesInConcernsAreTraits(): void
    {
        $this->assertClassesIn('app/Concerns')->areTraits();
    }

    #[Test]
    public function allClassesInContractsAreContracts(): void
    {
        $this->assertClassesIn('app/Contracts')->areContracts();
    }

    #[Test]
    public function allArtisanCommandsFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Console/Commands')
            ->areClasses()
            ->hasMethod('handle')
            ->extends(Command::class)
            ->hasSuffix('Command');
    }

    #[Test]
    public function allClassesInEnumsAreValidEnums(): void
    {
        $this->assertClassesIn('app/Enums')->areEnums();
    }

    #[Test]
    public function allEventsHaveAnEventSuffix(): void
    {
        $this->assertClassesIn('app/Events')->hasSuffix('Event');
    }

    #[Test]
    public function allExceptionsHaveAnExceptionSuffix(): void
    {
        $this->assertClassesIn('app/Exceptions')->hasSuffix('Exception');
    }

    #[Test]
    public function allFeedClassesFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Feeds')
            ->areClasses()
            ->extends(Feed::class)->except(Feed::class)
            ->hasSuffix('Feed');
    }

    #[Test]
    public function allControllersFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Http/Controllers')
            ->areClasses()
            ->hasSuffix('Controller')
            ->doesNotUseFunctions(['request'])
            ->areOnlyInvokable();
    }

    #[Test]
    public function allMiddlewareClassesFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Http/Middleware')
            ->areClasses()
            ->hasSuffix('Middleware')->except(HandleInertiaRequests::class)
            ->hasMethod('handle')->except(HandleInertiaRequests::class)
            ->doesNotUseFunctions(['request']);
    }

    #[Test]
    public function allHttpRequestClassesFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Http/Requests')
            ->areClasses()
            ->extends(FormRequest::class)
            ->hasMethod('rules')
            ->doesNotUseFunctions(['request'])
            ->hasSuffix('Request');
    }

    #[Test]
    public function allJobsFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Jobs')
            ->areClasses()
            ->hasSuffix('Job')
            ->implements(ShouldQueue::class);
    }

    #[Test]
    public function allEventListenersFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Listeners')
            ->areClasses()
            ->hasMethod('handle');
    }

    #[Test]
    public function allStaticMailClassesFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Mail')
            ->areClasses()
            ->hasSuffix('Mail')
            ->implements(ShouldQueue::class)
            ->extends(Mailable::class);
    }

    #[Test]
    public function allDynamicMailClassesFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Mailables')
            ->areClasses()
            ->hasSuffix('Mailable')
            ->extends(BaseMailable::class)->except(BaseMailable::class)
            ->hasMethod('toMail')->except([BaseMailable::class, BaseShopMailable::class]);
    }

    #[Test]
    public function allShopMailClassesExtendTheBaseShopMailable(): void
    {
        $this->assertClassesIn('app/Mailables/Shop')
            ->extends(BaseShopMailable::class)->except(BaseShopMailable::class);
    }

    #[Test]
    public function allModelsFollowTheCorrectFormat(): void
    {
        $this->assertClassesIn('app/Models')
            ->toNotHaveSuffix('Model')
            ->extend(Model::class)->except(User::class);
    }

    #[Test]
    public function allNotificationsFollowTheCorrectFormat(): void
    {
        $this->assertClassesIn('app/Notifications')
            ->hasSuffix('Notification')
            ->extend(Notification::class)
            ->hasMethod('toMail');
    }

    #[Test]
    public function allPipelineStepsFollowTheCorrectPattern(): void
    {
        $directories = [
            'app/Pipelines/EatingOut/DetermineNationwideBranchFromName/Steps',
            'app/Pipelines/EatingOut/GetEateries/Steps',
            'app/Pipelines/Search/Steps',
            'app/Pipelines/Shared/UploadTemporaryFile/Steps',
        ];

        foreach ($directories as $directory) {
            $this->assertClassesIn($directory)
                ->areClasses()
                ->hasMethod('handle');
        }
    }

    #[Test]
    public function checkRecommendedPlacePipelineStepsExtendTheBaseStepAction(): void
    {
        $this->assertClassesIn('app/Pipelines/EatingOut/CheckRecommendedPlace/Steps')
            ->extend(AbstractStepAction::class)->except(AbstractStepAction::class);
    }

    #[Test]
    public function allProvidersFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Providers')
            ->areClasses()
            ->hasSuffix('Provider')
            ->extend(ServiceProvider::class)->except(NovaServiceProvider::class);
    }

    #[Test]
    public function allQueriesFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Queries')
            ->areClasses()
            ->hasSuffix('Query')
            ->areInvokable();
    }

    #[Test]
    public function allJsonResourceCollectionsFollowTheCorrectFormat(): void
    {
        $this->assertClassesIn('app/ResourceCollections')
            ->areClasses()
            ->hasSuffix('Collection')
            ->extend(ResourceCollection::class);
    }

    #[Test]
    public function allJsonResourcesFollowTheCorrectFormat(): void
    {
        $this->assertClassesIn('app/Resources')
            ->areClasses()
            ->hasSuffix('Resource')
            ->extend(JsonResource::class)->except(ShopTravelCardProductResource::class)
            ->hasMethod('toArray')
            ->doesNotUseFunctions(['request']);
    }

    #[Test]
    public function allCustomRulesFollowTheCorrectFormat(): void
    {
        $this->assertClassesIn('app/Rules')
            ->areClasses()
            ->hasSuffix('Rule')
            ->implements(ValidationRule::class);
    }

    #[Test]
    public function allCustomQueryScopeClassesFollowTheCorrectFormat(): void
    {
        $this->assertClassesIn('app/Scopes')
            ->areClasses()
            ->hasSuffix('Scope')
            ->implements(Scope::class);
    }

    #[Test]
    public function allCustomSearchIndexesFollowTheCorrectFormat(): void
    {
        $this->assertClassesIn('app/Search')
            ->areClasses()
            ->extends(Aggregator::class)
            ->implements(IsSearchable::class);
    }

    #[Test]
    public function allAiPromptsFollowTheCorrectPattern(): void
    {
        $this->assertClassesIn('app/Support/Ai/Prompts')
            ->areClasses()
            ->hasSuffix('Prompt');
    }

    #[Test]
    public function allEatingOutSuggestEditableFieldsFollowTheCorrectFormat(): void
    {
        $this->assertClassesIn('app/Support/EatingOut/SuggestEdits/Fields')
            ->areClasses()
            ->extend(EditableField::class)->except(EditableField::class)
            ->hasSuffix('Field');
    }

    #[Test]
    public function allStateClassesFollowTheCorrectFormat(): void
    {
        $this->assertClassesIn('app/Support/State')
            ->areClasses()
            ->hasSuffix('State');
    }
}
