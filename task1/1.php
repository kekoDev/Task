<?php
class IQueuable
{
    private $queues = [];
    public function enqueue(string $string = null)
    {
        if (!isset($string)) {
            return "string is empty !";
        }
        array_push($this->queues, $string);
    }
    public function dequeue($first = false)
    {
        if ($first == true) {
            $l = array_shift($this->queues);
        } else {
            $l = array_pop($this->queues);
        }
        return $l; // return removed queue
    }
    public function getQueue($first = false)
    {
        return $this->queues;
    }
    public function size()
    {
        return count($this->queues);
    }
}
echo "\n--------IQueuable----------\n";
$q = new IQueuable();
echo "- Add new queues to list .";
$q->enqueue("test");
$q->enqueue("test1");
$q->enqueue("test2");
echo "\n- Number of items in the queue : ";
echo $q->size();
echo "\n- List of items in the queue : ";
print_r($q->getQueue());
echo "\n- remove queue (first-in first-out) : ";
echo $q->dequeue();
echo "\n- List of items in the queue after remove : ";
print_r($q->getQueue(true));
echo "\n--------IQueuable2----------\n";

class IQueuable2
{ // without using any array methods
    private $queues = [];
    function enqueue(string $string = null)
    {
        if (!isset($string)) {
            return "string is empty !";
        }
        $this->queues[] = $string;
        return $this->size(); // return Number of queues
    }
    function dequeue($first = false)
    {
        $l = false;
        if ($first == true) {
            foreach ($this->queues as $k => $v) {
                $l = $v;
                unset($this->queues[$k]);
                break;
            }
        } else {
            foreach ($this->queues as $k => $v) {
                $l = $v;
            }
            unset($this->queues[$k]);
        }
        return $l; // return removed queue
    }
    function getQueue()
    {
        return $this->queues;
    }
    function size()
    {
        $i = 0;
        foreach ($this->queues as $k => $v) {
            $i++;
        }
        return (int) $i;
    }
}
$q = new IQueuable2();
echo "- Add new queues to list.";
$q->enqueue("test");
$q->enqueue("test1");
$q->enqueue("test2");
echo "\n- Number of items in the queue : ";
echo $q->size();
echo "\n- List of items in the queue : ";
print_r($q->getQueue());
echo "\n- remove queue (last-in first-out) : ";
echo $q->dequeue(true);
echo "\n- List of items in the queue after remove : ";
print_r($q->getQueue(true));
