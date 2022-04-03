<?php

namespace App\Tests\Entity;

use App\Controller\ItemController;
use App\Entity\Item;
use App\Entity\TodoList;
use App\Entity\EmailSenderService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class TodoListTest extends TestCase
{
    private TodoList $todoList;
    private $emailSenderService;


    protected function setUp(): void
    {

        $this->todoList = new TodoList();
        $nbr_items = 7;
        for($i=0 ; $i<$nbr_items ; $i++){
            $this->todoList->addItem($this->createItem($i));
        }

        $this->emailSenderService = $this->getMockBuilder(EmailSenderService::class)
            ->onlyMethods(['sendEmail'])
            ->getMock();

        parent::setUp();
    }

    public function testIsValidNominal()
    {
        $this->assertTrue($this->todoList->isValid());
    }

    public function testNotValidDueToItemsTooBig()
    {
        for($i=7 ; $i<11 ; $i++){
            $this->todoList->addItem($this->createItem($i));
        }

        $this->assertFalse($this->todoList->isValid());
    }


    public function testIsAddItemNominal()
    {
        $item8 = $this->createItem(8);
        $this->todoList->addItem($item8);
        $this->assertContains($item8, $this->todoList->getItems());
    }

    public function testIsAddItemNominal8()
    {

        $item8 = $this->createItem(8);
        $this->todoList->addItem($item8);
        $this->emailSenderService->expects($this->once())
            ->method('sendEmail');

        $this->assertContains($item8, $this->todoList->getItems());
    }

    public function testNotAddItemDueToItemContain()
    {
        $item7 = $this->createItem(7);
        $this->todoList->addItem($item7);
        $this->assertCount(7, $this->todoList->getItems());
    }

    public function testNotAddItemDueToFast()
    {
        $item8 = $this->createItem(8);
        $this->todoList->addItem($item8);

        $item9 = $this->createItem(9);
        $item9->setDateAtCreated($item8->getDateAtCreated());
        $this->todoList->addItem($item9);

        $this->assertCount(8, $this->todoList->getItems());
    }

    private function createItem($i){
        $item = new Item();
        $item->setName("name_item"+$i);
        $item->setContent("content_item"+$i);
        $item->setDateAtCreated(Carbon::now()->addMinutes(31*$i));
        $item->setTodoList($this->todoList);
    }


}