<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Enquiry;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResolvesVerticalReferenceTest extends TestCase
{
    public function test_resolves_hotel_vertical()
    {
        $enquiry = new Enquiry();
        $enquiry->vertical = 'hotel';
        
        $relation = $enquiry->verticalModel();
        
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Hotel::class, $relation->getRelated());
        $this->assertEquals('reference_id', $relation->getForeignKeyName());
    }
    
    public function test_returns_null_for_general_vertical()
    {
        $enquiry = new Enquiry();
        $enquiry->vertical = 'general';
        
        $relation = $enquiry->verticalModel();
        
        $this->assertNull($relation);
    }
}
