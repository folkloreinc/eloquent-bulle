<?php namespace Folklore\EloquentBulle\Models;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Story extends Model implements SluggableInterface
{
    use SluggableTrait, SoftDeletingTrait;
    
    protected $table = 'stories';

    protected $dates = ['deleted_at','updated_at','created_at'];
    
    /**
     *
     * Relationships
     *
     */
    public function bulles()
    {
        return $this->belongsToMany('Folklore\EloquentBulle\Models\Bulle', 'stories_bulles', 'story_id', 'bulle_id')
                    ->withPivot('bulle_order')
                    ->orderBy('bulle_order', 'asc')
                    ->withTimestamps();
    }
    
    /**
     *
     * Sync methods
     *
     */
    public function syncBulles($items = array()) {

        $ids = array();
        
        if(is_array($items) && sizeof($items))
        {
            $order = 0;
            foreach($items as $item)
            {
                $model = null;
                if(!is_array($item))
                {
                    $model = Bulle::find($item);
                    if(!$model)
                    {
                        continue;
                    }
                }
                else
                {
                    $model = isset($item['id']) && !empty($item['id']) ? Bulle::find($item['id']):null;
                    if(!$model)
                    {
                        $model = new Bulle();
                    }
                    $model->fill($item);
                    $model->save();
                }
                
                $pivotData = array();
                $pivotData['bulle_order'] = $order;    
                $ids[$model->id] = $pivotData;
                $order++;
            }
        }
        
        $this->bulles()->sync($ids);

    }
    
}
