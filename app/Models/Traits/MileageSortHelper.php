<?php
namespace App\Models\Traits;

use Carbon\Carbon;
use Cache;
use DB;
use Arr;
use Illuminate\Support\Facades\Log;

use App\Models\UserData;

trait MileageSortHelper{
    
    /**
     * 计算全部用户的排名
     */
    public function cal_users_rank(){
        
        $start = microtime(true);
        Log::channel('calMileageInfoLogFile')->info("全部排名开始时间：".$start); 
        $max_mileage = DB::table('user_data')->max('mileage');
        $min_mileage = DB::table('user_data')->min('mileage');
        // 分n片
        $max = $max_mileage;
        $min = $min_mileage;
        $n = 10;
        // 分割平均区间
        $interval = ($max - $min) / $n;
        
        $temp = [];
        $temp_num = [];
        for($i = 0; $i < $n; $i++){
            $begin = $min+($i) * $interval;
            $end = ($min+($i) * $interval) + $interval+0.0001;
            $data = DB::table('user_data')
                    ->select('mileage')
                    ->having('mileage', '>=', $begin)
                    ->having('mileage', '<', $end)
                    ->groupBy('mileage')
                    ->get();
            $num = count($data);
            $temp_num[$i] = $num;
            
            $temp[$i] = [$begin, $end];
        }
        //将分片数组倒叙
        $temp_desc = array_reverse($temp,true);
        //分片数组对应的用户排名数量倒叙
        $temp_num_desc = array_reverse($temp_num,true);
        $list = [];
        
        //TODO 倒叙从最大里程分段开始执行，因为大的排名靠前
        foreach ($temp_desc as $key=>$value){        
            $begin_mileage = $value[0];
            $end_mileage = $value[1];
            $objectData = DB::table('user_data')
                    ->select('id','mileage')
                    ->where('mileage', '>=', $begin_mileage)
                    ->where('mileage', '<', $end_mileage)
                    ->orderBy('mileage','desc')
                    ->get();
            $list = json_decode($objectData);
            //Log::channel('calMileageInfoLogFile')->info(json_encode($objectData));
            if($list){
                $last_mileage = 0;
                $last_order_num = $this->calBeginOrderNum($temp_num_desc,$key);
                foreach ((array)$list as $k=>$v){
                    if(abs($v->mileage - $last_mileage)<0.000001){
                        $order_num = $last_order_num;
                    }else{
                        $order_num = $last_order_num+1;
                        $last_order_num = $order_num;
                        $last_mileage = $v->mileage;
                    }
                    
                    DB::update("update user_data set sort={$order_num} where id={$v->id}");
                }
                unset($list);
            }
        } 
        $end = microtime(true);
        Log::channel('calMileageInfoLogFile')->info("全部排名结束时间：".$end);  
        Log::channel('calMileageInfoLogFile')->info("全部排名耗费时间：".($end - $start));   
    }
    
    /**
     * 计算前面排名占用数量
     * @param type $temp_num
     * @param type $key
     * @return type
     */
    private function calBeginOrderNum($temp_num,$key){
        $arr = array_slice($temp_num,0,9-$key);
        return is_array($arr)? array_sum($arr):0;
    }
    
    /**
     * 计算有异动数据排名及受影响用户排名
     * @param type $oldMil
     * @param type $newMil
     * @param type $user_id
     */
    public function change_user_rank($oldMil,$newMil,$user_id){
        
        $start = microtime(true);
        Log::channel('calMileageInfoLogFile')->info("开始时间：".$start);
        Log::channel('calMileageInfoLogFile')->info("原里程：".$oldMil." 现里程:".$newMil." 用户ID:".$user_id);
        
        if($newMil > $oldMil){
           //TODO新里程 > 老里程
           $this->reset_user_rank_add($oldMil,$newMil,$user_id);
        }else{
           //TODO新里程 < 老里程
            $this->reset_user_rank_sub($oldMil,$newMil,$user_id);
        }
        
        $end = microtime(true);
        Log::channel('calMileageInfoLogFile')->info("结束时间：".$end);
        
        $span = $end - $start;
        Log::channel('calMileageInfoLogFile')->info("间隔时间s：".$span);
    }
    
    /**
     * 新里程 > 老里程
     * @param type $oldMil
     * @param type $newMil
     * @param type $user_id
     */
    private function reset_user_rank_add($oldMil,$newMil,$user_id){
        $objectData = DB::table('user_data')
                    ->select('id','mileage','user_id','sort')
                    ->where('mileage', '>=', $oldMil)
                    ->where('mileage', '<', $newMil)
                    ->get();
        $list = json_decode($objectData);
        if($list){
            //TODO 找出最大里程对应的排名
            $max_mil = $max_sort = 0;
            foreach ($list as $key => $value) {
                if($value->mileage > $max_mil){
                    $max_mil = $value->mileage;
                    $max_sort = $value->sort;
                }
            }
            
            if($max_sort){
                $curr_sort = null;
                //TODO 新的里程排名是否占位
                $data = DB::table('user_data')->select('id','mileage','user_id','sort')->where('mileage','=',$newMil)->where('user_id','!=',$user_id)->get();
                $data = json_decode($data);
                //TODO 老的里程排名是否占位
                $oldData = DB::table('user_data')->select('id','mileage','user_id','sort')->where('mileage','=',$oldMil)->where('user_id','!=',$user_id)->get();
                $oldData = json_decode($oldData);
                
                if($data && $data[0]->id > 0){
                    $curr_sort = $data[0]->sort;
                    Log::channel('calMileageInfoLogFile')->info("有里程相同的排名，不需要更新区间内的排名");
                    
                    if(!$oldData){
                        //TODO 如果没有相同老的里程记录，即老的排名没被占用，
                        //则排名变更后，原来排名就没有数据了，所以小于$oldMil的排名都要向前移一位
                        Log::channel('calMileageInfoLogFile')->info("没有老里程相同的排名，更新比老里程小的排名+1");
                        DB::update("update user_data set sort=sort-1 where mileage < $oldMil");
                    }
                }else{
                    $curr_sort = $max_sort;

                    if(!$oldData){
                        Log::channel('calMileageInfoLogFile')->info("有老里程相同的排名，更新比老里程大的，比新里程小的用户排名+1");
                        DB::update("update user_data set sort=sort+1 where mileage < $newMil and mileage>$oldMil");
                    }else{
                        Log::channel('calMileageInfoLogFile')->info("没有老里程相同的排名，更新比老里程小的排名+1");
                        DB::update("update user_data set sort=sort+1 where mileage < $newMil");
                    }
                    
                }
                Log::channel('calMileageInfoLogFile')->info("***current_sort*：".$curr_sort);
                DB::table('user_data')->where('user_id', $user_id)->update(['sort' => $curr_sort]);
            }
        }
    }
    
    /**
     * 新里程 < 老里程
     * @param type $oldMil
     * @param type $newMil
     * @param type $user_id
     */
    private function reset_user_rank_sub($oldMil,$newMil,$user_id){
        
        $objectData = DB::table('user_data')
                    ->select('id','mileage','user_id','sort')
                    ->where('mileage', '>', $newMil)
                    ->where('mileage', '<=', $oldMil)
                    ->get();
        $list = json_decode($objectData);
        if($list){
            //TODO 找出最小里程对应的排名
            $min_mil = $min_sort = 0;
            foreach ($list as $key => $value) {
                if($min_mil == 0 || $value->mileage < $min_mil){
                    $min_mil = $value->mileage;
                    $min_sort = $value->sort;
                }
            }
            
             if($max_sort){
                $curr_sort = null;
                //TODO 新的里程排名是否占位
                $data = DB::table('user_data')
                        ->select('id','mileage','user_id','sort')
                        ->where('mileage','=',$newMil)
                        ->where('user_id','!=',$user_id)
                        ->get();
                $data = json_decode($data);
                
                 //TODO 老的里程排名是否占位
                $oldData = DB::table('user_data')
                            ->select('id','mileage','user_id','sort')
                            ->where('mileage','=',$oldMil)
                            ->where('user_id','!=',$user_id)
                            ->get();
                $oldData = json_decode($oldData);
                
                if($data && $data[0]->id > 0){//TODO 新的里程排名已被占用
                    $curr_sort = $data[0]->sort;//新里程的排名
                    Log::channel('calMileageInfoLogFile')->info("有里程相同的排名：".$curr_sort);
                    
                    if(!$oldData){
                        //TODO 如果没有相同老的里程记录，即老的排名没被占用，
                        //则排名变更后，原来排名就没有数据了，所以小于$oldMil的排名都要向前移一位
                        Log::channel('calMileageInfoLogFile')->info("没有老里程相同的排名，更新比老里程小的排名-1,即排名向前一位");
                        DB::update("update user_data set sort=sort-1 where mileage < $oldMil");
                    }
                }else{//TODO 新的里程排名不存在
                    $curr_sort = $min_sort+1;
                    Log::channel('calMileageInfoLogFile')->info("没有新里程相同的排名，取区间最小的排名：".$curr_sort);
                    if(!$oldData){
                        Log::channel('calMileageInfoLogFile')->info("更新比老里程大小的用户排名-1");
                        DB::update("update user_data set sort=sort-1 where mileage < $oldMil and mileage>$newMil");
                    }else{
                        Log::channel('calMileageInfoLogFile')->info("有老里程相同的排名，更新比老里程小的排名+1,即排名向后一位");
                        DB::update("update user_data set sort=sort+1 where mileage < $newMil");
                    }
                }
                DB::table('user_data')->where('user_id', $user_id)->update(['sort' => $curr_sort]);
             } 
        }
        
    }
    
    
    public function get_rank_100(){
        
        $start = microtime(true);
        Log::channel('calMileageInfoLogFile')->info("取排名前100名开始时间：".$start); 
        
        DB::enableQueryLog();
//        $resut = DB::select('select max(mileage) as max,min(mileage) as min from user_data');
//        $right_node = $resut[0]->max;
//        $left_node = $resut[0]->min;
//        
//        $i = 1;
//        do{
//            $tree_nodes = $this->getNodes($left_node, $right_node);
//            $num = DB::table('user_data')
//                    ->where('mileage', '>=', $tree_nodes[1][0])
//                    ->where('mileage', '<=', $tree_nodes[1][1])
//                    ->count();
//            $left_node = $tree_nodes[1][0];
//            $right_node = $tree_nodes[1][1];
//            
//            $i++;
//        }while($num > 1500);
//        
//        if($num<1000){
//            $left_node = $tree_nodes[0][0];
//        }

        
        $list = DB::table('user_data')
            ->select('id','mileage','user_id','sort')
//            ->where('mileage', '>=', $left_node)
//            ->where('mileage', '<=', $right_node)
            ->orderby('mileage','desc')
            ->take(1500)
            ->get()
            ->toArray();
        
        $rank = 1;
        $last_mileage = 0;
        $last_order_num = 0;
        $order_list = [];
        foreach ((array)$list as $key=>$value){
            if(!$last_mileage || $value->mileage!=$last_mileage){
                $value->order_num = $rank;
                $last_order_num = $rank;
                $last_mileage = $value->mileage;
                $rank++;
            }else{
                $value->order_num = $last_order_num;
                $last_mileage = $value->mileage;
            }
            $order_list[] = $value;
            if($rank>1000){
                break;
            }
            
        }
        
        //print_r($order_list);
        Log::channel('calMileageInfoLogFile')->info("sql：".json_encode(DB::getQueryLog()));
        
        $end = microtime(true);
        Log::channel('calMileageInfoLogFile')->info("结束时间：".$end);
        
        $span = $end - $start;
        Log::channel('calMileageInfoLogFile')->info("间隔时间s：".$span);
        
        //print_r($list);
        
        
    }
    
    private function getNodes($left_node,$right_node){
        $middle_node = ($right_node - $left_node)/2 ;
        return [[$left_node,$middle_node+$left_node],[$middle_node+$left_node,$right_node]];
    }
    
    
}
