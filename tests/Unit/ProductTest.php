<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    public function test_product_has_base_price()
    {
        $product = Product::factory()->create([
            'price' => 100.00
        ]);

        $this->assertEquals(100.00, $product->price);
    }

    public function test_member_gets_discount()
    {
        $product = Product::factory()->create([
            'price' => 100.00
        ]);

        $member = User::factory()->create([
            'role' => 'member'
        ]);

        $this->assertEquals(90.00, $product->getPriceForUser($member));
    }

    public function test_customer_gets_base_price()
    {
        $product = Product::factory()->create([
            'price' => 100.00
        ]);

        $customer = User::factory()->create([
            'role' => 'customer'
        ]);

        $this->assertEquals(100.00, $product->getPriceForUser($customer));
    }

    public function test_guest_gets_base_price()
    {
        $product = Product::factory()->create([
            'price' => 100.00
        ]);

        $this->assertEquals(100.00, $product->getPriceForUser(null));
    }
}