<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Vendor;

class VendorApiTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * setup
     *  - seed the database on setup
     */
    public function setup() {
        parent::setup();

        $this->artisan('migrate');
        $this->artisan('db:seed');
    }

    /**
     * Admin exists via api GET
     *
     * @return void
     */
    public function testExists()
    {
        $this->get('/api/vendor')
             ->seeJson([
                 'id' => 1,
                 'email' => 'vendors@vendorify.com'
             ]);
    }

    /**
     * Vendor create via api POST
     *
     * @return void
     */
    public function testCreate()
    {
        $vendor = factory(App\Vendor::class)->make();
        $vendor = $vendor->toArray();
        unset($vendor['id']);
        $this->post('/api/vendor', $vendor)
             ->seeJson([
                 'name' => $vendor['name'],
                 'business' => $vendor['business']
             ]);
    }

    /**
     * Vendor update via api PUT
     *
     * @return void
     */
    public function testUpdate()
    {
        $vendor = factory(App\Vendor::class)->create();
        $name = str_random(10);
        $this->put('/api/vendor/'.$vendor->id, ['name' => $name])
             ->seeJson([
                 'name' => $name
             ]);
    }

    /**
     * Vendor delete via api DELETE
     *
     * @return void
     */
    public function testDelete()
    {
        $vendor = factory(App\Vendor::class)->create();
        $this->delete('/api/vendor/'.$vendor->id)
             ->seeJson([
                 'status' => 'success'
             ]);

        // check db
        $vendor = Vendor::find($vendor->id);
        $this->assertNull($vendor);
    }

    /**
     * tearDown
     *  - rollback migrations
     */
    public function tearDown() {
        parent::setup();
        
        $this->artisan('migrate:rollback');
    }
}
