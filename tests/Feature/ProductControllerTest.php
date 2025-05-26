<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products()
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_member_sees_discounted_prices()
    {
        $member = User::factory()->create(['role' => 'member']);
        $product = Product::factory()->create(['price' => 100]);

        $response = $this->actingAs($member)
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.price', 90.00);
    }

    public function test_can_create_product()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100,
            'stock' => 10
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/products', $productData);

        $response->assertStatus(201)
            ->assertJsonFragment($productData);
    }

    public function test_can_update_product()
    {
        $user = User::factory()->create(['role' => 'member']);
        $product = Product::factory()->create();
        $updateData = [
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => 200,
            'stock' => 20
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product updated successfully',
                'product' => [
                    'name' => 'Updated Product',
                    'description' => 'Updated Description',
                    'price' => '200.00',
                    'stock' => 20
                ]
            ]);
    }

    public function test_can_delete_product()
    {
        $user = User::factory()->create(['role' => 'member']);
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}