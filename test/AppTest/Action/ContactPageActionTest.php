<?php
/**
 * @file
 *
 */

namespace AppTest\Action;

use App\Action\ContactPageAction;
use AppTest\WebTestCase;
use Zend\Mail\Message;
use Zend\Mail\Transport\Exception\RuntimeException;
use Zend\Mail\Transport\TransportInterface;

class ContactPageActionTest extends WebTestCase
{
    protected function getValidData()
    {
        return [
            'name' => 'John Doe',
            'email' => 'john.doe@email.com',
            'website' => 'http://example.com',
            'message' => 'Lorem ipsum dolor sit amet'
        ];
    }

    public function testContactPageAccess()
    {
        $response = $this->handleRequest('GET', '/contact');
        $this->assertEquals(200, $response->getStatusCode());
        $crawler = $this->crawler($response);
        $this->assertEquals(1, $crawler->filterXPath('//form[@id="contact_form"]')->count(), 'Form "contact_form" is expected');
        $this->assertEquals(1, $crawler->filterXPath('//h2[contains(., "Contact Us Today!")]')->count(), 'Title "Contact Us Today!" is expected');
    }

    public function testContactPageFormSubmit()
    {
        // Mock mail transport.
        $container = self::$container;
        $container->setAllowOverride(true);
        $mail = $this->getMockForAbstractClass(TransportInterface::class, ['send']);
        $mail->expects($this->once())->method('send')->with($this->callback([$this, 'assertMessage']));
        $container->setService(TransportInterface::class, $mail);
        // Re-create tested class.
        $container->setService(ContactPageAction::class, $container->build(ContactPageAction::class));
        // Main test.
        $response = $this->handleRequest('POST', '/contact', $this->getValidData());
        $this->assertEquals(200, $response->getStatusCode());
        $crawler = $this->crawler($response);
        $this->assertEquals(1, $crawler->filterXPath('html[contains(., "Thanks for contacting us, we will get back to you shortly.")]')->count(), 'Thank you message is expected');
    }

    public function assertMessage(Message $message)
    {
        $fields = [
            'name' => FILTER_SANITIZE_ENCODED,
            'email' => FILTER_SANITIZE_EMAIL,
            'website' => FILTER_SANITIZE_URL,
            'message' => FILTER_SANITIZE_ENCODED,
        ];
        $data = filter_var_array($this->getValidData(), $fields, true);
        $this->assertTrue($message->getReplyTo()->has($data['email']));
        $this->assertEquals($message->getSubject(), 'Contact form message');
        $this->assertEquals($message->getBodyText(), $data['message']);
        return $message;
    }

    public function testContactPageFormSubmitEmailSendFailed()
    {
        // Mock mail transport.
        $container = self::$container;
        $container->setAllowOverride(true);
        $mail = $this->getMockForAbstractClass(TransportInterface::class, ['send']);
        $mail->expects($this->once())->method('send')->willThrowException(new RuntimeException());
        $container->setService(TransportInterface::class, $mail);
        // Re-create tested class.
        $container->setService(ContactPageAction::class, $container->build(ContactPageAction::class));
        // Main test.
        $response = $this->handleRequest('POST', '/contact', $this->getValidData());
        $this->assertEquals(200, $response->getStatusCode());
        $crawler = $this->crawler($response);
        $this->assertEquals(1, $crawler->filterXPath('html[contains(., "Sorry, email was not sent because site administrator did not configure it =(")]')->count(), 'Email send failure message is expected');
    }

    protected function prepareObjectForValidateData($data = [])
    {
        // Mock mail transport.
        $container = self::$container;
        $container->setAllowOverride(true);
        $mail = $this->getMockForAbstractClass(TransportInterface::class, ['send']);
        $mail->expects($this->never())->method('send');
        $container->setService(TransportInterface::class, $mail);
        // Re-create tested class.
        $container->setService(ContactPageAction::class, $container->build(ContactPageAction::class));
        // Main test.
        $response = $this->handleRequest('POST', '/contact', $data);
        $this->assertEquals(200, $response->getStatusCode());
        return $this->crawler($response);
    }

    public function testValidateDataAllEmpty()
    {
        $crawler = $this->prepareObjectForValidateData();
        $this->assertEquals(1, $crawler->filterXPath('html[contains(., "All fields are required")]')->count(), '"All fields are required" message is expected');
    }

    public function testValidateDataNameIsNotLessThanTwoSymbols()
    {
        $crawler = $this->prepareObjectForValidateData(['name' => 'ab']);
        $this->assertEquals(1, $crawler->filterXPath('html[contains(., "Name is not valid, we think you can\'t have a name with 2 symbols")]')->count(), '"Name is not valid" message is expected');
    }

    public function testValidateDataNameIsNotGreaterThanHundredSymbols()
    {
        $crawler = $this->prepareObjectForValidateData(['name' => str_repeat('a', 101)]);
        $this->assertEquals(1, $crawler->filterXPath('html[contains(., "Name is not valid, we think you can\'t have a name with 101 symbols.")]')->count(), '"Name is not valid" message is expected');
    }

    public function testValidateDataEmailIsNotValid()
    {
        $crawler = $this->prepareObjectForValidateData(['email' => 'some-invalid-email']);
        $this->assertEquals(1, $crawler->filterXPath('html[contains(., "Email address is not valid")]')->count(), '"Email address is not valid" message is expected');
    }

    public function testValidateDataWebsiteIsNotValid()
    {
        $crawler = $this->prepareObjectForValidateData(['website' => 'some-invalid-website']);
        $this->assertEquals(1, $crawler->filterXPath('html[contains(., "URL address is not valid")]')->count(), '"URL address is not valid" message is expected');
    }

    public function testValidateDataMessageIsNotLessThanTenSymbols()
    {
        $crawler = $this->prepareObjectForValidateData(['message' => str_repeat('a', 9)]);
        $this->assertEquals(1, $crawler->filterXPath('html[contains(., "Please enter the message text at least 10 characters and no more than 200.")]')->count(), '"Message is not valid" message is expected');
    }

    public function testValidateDataMessageIsNotGreaterThanTwoHundredsSymbols()
    {
        $crawler = $this->prepareObjectForValidateData(['message' => str_repeat('a', 201)]);
        $this->assertEquals(1, $crawler->filterXPath('html[contains(., "Please enter the message text at least 10 characters and no more than 200.")]')->count(), '"Message is not valid" message is expected');
    }
}
