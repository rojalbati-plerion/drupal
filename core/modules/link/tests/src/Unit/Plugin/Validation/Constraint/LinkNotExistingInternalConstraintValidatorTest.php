<?php

declare(strict_types=1);

namespace Drupal\Tests\link\Unit\Plugin\Validation\Constraint;

use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Drupal\link\LinkItemInterface;
use Drupal\link\Plugin\Validation\Constraint\LinkNotExistingInternalConstraint;
use Drupal\link\Plugin\Validation\Constraint\LinkNotExistingInternalConstraintValidator;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Tests Drupal\link\Plugin\Validation\Constraint\LinkNotExistingInternalConstraintValidator.
 */
#[CoversClass(LinkNotExistingInternalConstraintValidator::class)]
#[Group('Link')]
class LinkNotExistingInternalConstraintValidatorTest extends UnitTestCase {

  /**
   * Tests validate from uri.
   *
   * @legacy-covers ::validate
   */
  public function testValidateFromUri(): void {
    $url = Url::fromUri('https://www.drupal.org');

    $link = $this->createMock(LinkItemInterface::class);
    $link->expects($this->any())
      ->method('getUrl')
      ->willReturn($url);

    $context = $this->createMock(ExecutionContextInterface::class);
    $context->expects($this->never())
      ->method('addViolation');

    $this->validate($link, $context);
  }

  /**
   * Tests validate from route.
   *
   * @legacy-covers ::validate
   */
  public function testValidateFromRoute(): void {
    $url = Url::fromRoute('example.existing_route');

    $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
    $urlGenerator->expects($this->any())
      ->method('generateFromRoute')
      ->with('example.existing_route', [], [])
      ->willReturn('/example/existing');
    $url->setUrlGenerator($urlGenerator);

    $link = $this->createMock(LinkItemInterface::class);
    $link->expects($this->any())
      ->method('getUrl')
      ->willReturn($url);

    $context = $this->createMock(ExecutionContextInterface::class);
    $context->expects($this->never())
      ->method('addViolation');

    $this->validate($link, $context);
  }

  /**
   * Tests validate from non existing route.
   *
   * @legacy-covers ::validate
   */
  public function testValidateFromNonExistingRoute(): void {
    $url = Url::fromRoute('example.not_existing_route');

    $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
    $urlGenerator->expects($this->any())
      ->method('generateFromRoute')
      ->with('example.not_existing_route', [], [])
      ->willThrowException(new RouteNotFoundException());
    $url->setUrlGenerator($urlGenerator);

    $link = $this->createMock(LinkItemInterface::class);
    $link->expects($this->any())
      ->method('getUrl')
      ->willReturn($url);

    $context = $this->createMock(ExecutionContextInterface::class);
    $context->expects($this->once())
      ->method('addViolation');

    $this->validate($link, $context);
  }

  /**
   * Tests validate with malformed uri.
   *
   * @see \Drupal\Core\Url::fromUri
   * @legacy-covers ::validate
   */
  public function testValidateWithMalformedUri(): void {
    $link = $this->createMock(LinkItemInterface::class);
    $link->expects($this->any())
      ->method('getUrl')
      ->willThrowException(new \InvalidArgumentException());

    $context = $this->createMock(ExecutionContextInterface::class);
    $context->expects($this->never())
      ->method('addViolation');

    $this->validate($link, $context);
  }

  /**
   * Validate the link.
   */
  protected function validate(LinkItemInterface&MockObject $link, ExecutionContextInterface&MockObject $context): void {
    $validator = new LinkNotExistingInternalConstraintValidator();
    $validator->initialize($context);
    $validator->validate($link, new LinkNotExistingInternalConstraint());
  }

}
