<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/15/14
 * Time: 11:45 AM
 */

class PatientScanAttachment implements JsonSerializable {
    private $id;
    private $patient_scan;
    private $attachment;
    private $attachmentURL;
    private $note;

    private $dateAdded;
    private $creator;

    function __construct($id=NULL){
        $this->id = $id;
    }

    public function getCreator(){
        return $this->creator;
    }

    public function setCreator($creator){
        $this->creator = $creator;
    }

    public function getDateAdded(){
        return $this->dateAdded;
    }

    public function setDateAdded($dateAdded){
        $this->dateAdded = $dateAdded;
    }
    /**
     * @return mixed
     */
    public function getAttachmentURL()
    {
        return $this->attachmentURL;
    }

    /**
     * @param mixed $attachmentURL
     */
    public function setAttachmentURL($attachmentURL)
    {
        $this->attachmentURL = $attachmentURL;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getPatientScan()
    {
        return $this->patient_scan;
    }

    /**
     * @param mixed $patient_scan
     */
    public function setPatientScan($patient_scan)
    {
        $this->patient_scan = $patient_scan;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return mixed
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param mixed $attachment
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
    }


    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return (object) get_object_vars($this);
    }


} 