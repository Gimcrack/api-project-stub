<?php

namespace Tests;

use App\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Assert;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $response = null;
    protected $headers = [];
    protected $api_prefix = "api/v1";
    protected $api = false;

    protected function setUp()
    {
        parent::setUp();
        Collection::macro('assertContains', function ($value) {
            Assert::assertTrue($this->contains($value), "Failed to assert that the collection contains the expected value");
            return $this;
        });

        Collection::macro('assertEmpty', function () {
            Assert::assertCount(0, $this, "Failed to assert that the collection was empty");
            return $this;
        });

        Collection::macro('assertCount', function ($count) {
            Assert::assertCount($count, $this, "Failed to assert that the collection had the expected count");
            return $this;
        });

        Collection::macro('assertNotEmpty', function () {
            Assert::assertTrue($this->count() > 0, "Failed to assert that the collection was not empty");
            return $this;
        });

        Collection::macro('assertMinCount', function ($count) {
            Assert::assertTrue($this->count() >= $count, "Failed to assert that the collection had at least {$count} items");
            return $this;
        });
    }

    /**
     * Create an admin and login
     * @method actingAsAdmin
     *
     * @return   $this
     */
    public function actingAsAdmin()
    {
        $user = create_state(User::class,'admin');

        return $this->actingAs($user);
    }

    /**
     * Create a user and login
     * @method actingAsUser
     *
     * @return   $this
     */
    public function actingAsUser()
    {
        $user = create(User::class);

        return $this->actingAs($user);
    }

    /**
     * Login and make the request as the selected user
     *
     * @param Authenticatable|User $user
     * @param string|null $driver
     * @return $this
     */
    public function actingAs(Authenticatable $user, $driver = null)
    {
        parent::actingAs($user, $driver);

        $this->headers(['Authorization' => "Bearer {$user->api_token}"]);

        return $this;
    }

    /**
     * Get the response
     *
     * @return     TestResponse
     */
    public function response()
    {
        if ( ! $this->response) {
            $this->fail("No response yet");
        }
        return $this->response;
    }

    /**
     * Assert that the json data has the specified count
     * @method assertJsonCount
     *
     * @param $count
     *
     * @return $this
     */
    public function assertJsonCount($count)
    {
        $this->assertCount($count, $this->response()->json());
        return $this;
    }

    /**
     * Set some headers
     *
     * @param      array $headers The headers
     *
     * @return $this
     */
    public function headers($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set the referrer header
     * @method from
     *
     * @param string $from
     *
     * @return $this
     */
    public function from(string $from)
    {
        return $this->headers(['referer' => $from]);
    }

    /**
     * Access an api endpoint
     * @method api
     *
     * @return $this
     */
    public function api()
    {
        $this->api = true;
        return $this;
    }

    /**
     * Format the desired endpoint
     * @param $endpoint string  The application endpoint to access
     *
     * @return string
     */
    private function endpoint($endpoint)
    {
        return ($this->api) ?
            vsprintf("%s/%s", [$this->api_prefix, trim($endpoint, "/")]) :
            $endpoint;
    }

    /**
     * Post to some endpoint with some data and save the response
     * @method post
     *
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     *
     * @return $this
     */
    public function post($endpoint, array $data = [], array $headers = [])
    {
        $this->headers = array_merge($this->headers, $headers);
        $this->response = parent::json('POST', $this->endpoint($endpoint), $data, $this->headers);
        $this->api = false;
        return $this;
    }

    /**
     * Get some endpoint with some data and save the response
     * @method get
     *
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     *
     * @return $this
     */
    public function get($endpoint, array $data = [], array $headers = [])
    {
        $this->headers = array_merge($this->headers, $headers);
        $this->response = parent::json('GET', $this->endpoint($endpoint), $data, $this->headers);
        $this->api = false;
        return $this;
    }

    /**
     * Patch some endpoint with some data and save the response
     * @method patch
     *
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     *
     * @return $this
     */
    public function patch($endpoint, array $data = [], array $headers = [])
    {
        $this->headers = array_merge($this->headers, $headers);
        $this->response = parent::json('PATCH', $this->endpoint($endpoint), $data, $this->headers);
        $this->api = false;
        return $this;
    }

    /**
     * Put some endpoint with some data and save the response
     * @method put
     *
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     *
     * @return $this
     */
    public function put($endpoint, array $data = [], array $headers = [])
    {
        $this->headers = array_merge($this->headers, $headers);
        $this->response = parent::json('PUT', $this->endpoint($endpoint), $data, $this->headers);
        $this->api = false;
        return $this;
    }

    /**
     * Delete some endpoint with some data and save the response
     * @method delete
     *
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     *
     * @return $this
     */
    public function delete($endpoint, array $data = [], array $headers = [])
    {
        $this->headers = array_merge($this->headers, $headers);
        $this->response = parent::json('DELETE', $this->endpoint($endpoint), $data, $this->headers);
        $this->api = false;
        return $this;
    }

    /**
     * Assert that the event was dispatched and has the proper data
     * @method assertEvent
     *
     * @return      $this
     */
    public function assertEvent($event, $models = [])
    {
        Event::assertDispatched($event, function($e) use ($models) {
            // make sure the event has the expected models
            foreach($models as $model_type => $model) {
                if ( ! is_object($model) ) {
                    if ( $e->$model_type != $model ) return false;
                }
                else {
                    if ( ! $e->$model_type->is($model) ) return false;
                }

            }
            return true;
        });
        return $this;
    }

    /**
     * Assert that the event was dispatched and has the proper data
     * @method assertEvent
     *
     * @return      $this
     */
    public function assertNotEvent($event)
    {
        Event::assertNotDispatched($event);
        return $this;
    }

    /**
     * Assert that the job was pushed onto the queue
     *
     * @param $job
     * @param array $models
     * @return $this
     */
    public function assertJob($job, $models = [])
    {
        Queue::assertPushed($job, function($j) use ($models) {
            // make sure the event has the expected models
            foreach($models as $model_type => $model) {
                if ( ! $j->$model_type->is($model) ) return false;
            }
            return true;
        });

        return $this;
    }
}