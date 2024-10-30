<?php
/**
 * https://pods.io/docs/code/pods/fetch/
 */
namespace CivicLookup;

/**
 *
 */
class PodRecord extends PodTable {
    public $podName;
    public $id;
    public $data;

    function __construct($id=false) {
        $this->podName = $podName;
        $this->id = $id;
        $pods = pods($this->podName);
        if($this->id) {
            $this->data = (object)$pods->fetch($this->id);
        }

    }
    /**
     * https://staging-devautosafety.kinsta.cloud/wp-admin/post.php?post=1184683&action=edit
     */

    function get() {
        $debug = false;
        if($debug) {
            dump('PodRecord get');
            dump($this);
        }

        return $this->data;
    }
}
