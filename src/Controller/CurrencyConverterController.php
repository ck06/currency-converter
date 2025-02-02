<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CurrencyDto;
use App\Entity\Currency;
use App\Repository\CurrencyRepository;
use App\Service\CurrencyConverter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use HttpRuntimeException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CurrencyConverterController extends AbstractController
{
    private const DROPDOWN = 'currencyChoice';
    private const INPUT = 'currencyAmount';

    public function __construct(
        private EntityManagerInterface $em,
        private CurrencyConverter $converter,
    ) {
    }

    #[Route('/', name: 'currency-converter')]
    public function load(Request $request): Response
    {
        $form = $this->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $results = $this->converter->convertAll($data[self::DROPDOWN], (float)$data[self::INPUT]);
        }

        return $this->render('converter/converter.html.twig', [
            'form' => $form->createView(),
            'results' => $results ?? [],
        ]);
    }

    private function getForm(): FormInterface
    {
        return $this->createFormBuilder()
            ->add(
                self::DROPDOWN,
                ChoiceType::class,
                [
                    'label' => 'From',
                    'choices' => array_flip($this->getChoices()),
                    'attr' => ['class' => 'form-select'],
                    'placeholder' => 'Choose a currency',
                ],
            )->add(
                self::INPUT,
                TextType::class,
                [
                    'label' => 'Amount',
                    'attr' => ['class' => 'form-control'],
                ]
            )->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'Convert',
                    'attr' => ['class' => 'btn btn-primary mt-3'],
                ]
            )->getForm();
    }

    private function getChoices(): array
    {
        $options = [];

        /** @var CurrencyRepository $repository */
        $repository = $this->em->getRepository(Currency::class);

        /** @var Currency $currency */
        foreach ($repository->findAll() as $currency) {
            $options[(int)$currency->getNumericCode()] = $currency->getAlphaCode();
        }

        return $options;
    }
}
