<?php

namespace spec\Sylake\Sylakim\Connector\Step;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Sylake\Sylakim\Connector\Client\AssociationTypeClientFactoryInterface;
use Sylake\Sylakim\Connector\Client\ResourceClientInterface;
use Sylake\Sylakim\Connector\Client\Url;
use Sylake\Sylakim\Connector\Synchronizer\AssociationTypeSynchronizerInterface;

final class AssociationTypeStepSpec extends ObjectBehavior
{
    function let(
        AssociationTypeRepositoryInterface $associationTypeRepository,
        AssociationTypeClientFactoryInterface $associationTypeClientFactory,
        AssociationTypeSynchronizerInterface $associationTypeSynchronizer
    ) {
        $this->beConstructedWith('step name', $associationTypeRepository, $associationTypeClientFactory, $associationTypeSynchronizer);
    }

    function it_is_an_akeneo_step()
    {
        $this->shouldImplement(StepInterface::class);
    }

    function it_has_name()
    {
        $this->getName()->shouldReturn('step name');
    }

    function it_synchronizes_association_types(
        AssociationTypeRepositoryInterface $associationTypeRepository,
        AssociationTypeClientFactoryInterface $associationTypeClientFactory,
        AssociationTypeSynchronizerInterface $associationTypeSynchronizer,
        ResourceClientInterface $associationTypeClient,
        AssociationTypeInterface $firstAssociationType,
        AssociationTypeInterface $secondAssociationType,
        StepExecution $stepExecution
    ) {
        $stepExecution->getJobParameters()->willReturn(new JobParameters([
            'api_url' => 'http://sylius.org',
            'api_public_id' => 'public id',
            'api_secret' => 'secret',
            'admin_login' => 'login',
            'admin_password' => 'password',
        ]));

        $associationTypeClientFactory
            ->create(Url::fromString('http://sylius.org'), 'public id', 'secret', 'login', 'password')
            ->willReturn($associationTypeClient)
        ;

        $associationTypeRepository->findAll()->willReturn([
            $firstAssociationType,
            $secondAssociationType,
        ]);

        $associationTypeSynchronizer->synchronize($associationTypeClient, $firstAssociationType)->shouldBeCalled();
        $associationTypeSynchronizer->synchronize($associationTypeClient, $secondAssociationType)->shouldBeCalled();

        $this->execute($stepExecution);
    }
}
