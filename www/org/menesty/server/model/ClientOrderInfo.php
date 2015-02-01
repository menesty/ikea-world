<?php

/**
 * User: Menesty
 * Date: 1/13/15
 * Time: 09:00
 */
class ClientOrderInfo
{
    private $id;
    private $firstName;
    private $lastName;
    private $email;
    private $telephone;
    private $address;
    private $city;
    private $post_code;
    private $region_state;
    private $comment;
    private $country;

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
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getPostCode()
    {
        return $this->post_code;
    }

    /**
     * @param mixed $post_code
     */
    public function setPostCode($post_code)
    {
        $this->post_code = $post_code;
    }

    /**
     * @return mixed
     */
    public function getRegionState()
    {
        return htmlspecialchars($this->region_state);
    }

    /**
     * @param mixed $region_state
     */
    public function setRegionState($region_state)
    {
        $this->region_state = $region_state;
    }

    /**
     * @return mixed
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @param mixed $telephone
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }


    public function isValid()
    {
        $error = array();

        if (strlen($this->firstName) < 3 || strlen($this->firstName) > 20) {
            $error["firstName"] = Language::getCheckoutErrorLabel("firstName");
        }

        if (strlen($this->lastName) < 3 || strlen($this->lastName) > 20) {
            $error["lastName"] = Language::getCheckoutErrorLabel("lastName");
        }

        if (strlen($this->telephone) < 4 || strlen($this->telephone) > 20) {
            $error["telephone"] = Language::getCheckoutErrorLabel("telephone");
        }

        if (strlen($this->address) < 4 || strlen($this->address) > 100) {
            $error["address"] = Language::getCheckoutErrorLabel("address");
        }

        if (strlen($this->post_code) < 2 || strlen($this->post_code) > 8) {
            $error["post_code"] = Language::getCheckoutErrorLabel("post_code");
        }

        if (strlen($this->city) < 3 || strlen($this->city) > 30) {
            $error["city"] = Language::getCheckoutErrorLabel("city");
        }

        $countries = Language::getCheckoutLabel('countries');

        if (!in_array($this->country, $countries)) {
            $error["country"] = Language::getCheckoutErrorLabel("country");
        }

        if (strlen($this->region_state) < 3 || strlen($this->region_state) > 30) {
            $error["region_state"] = Language::getCheckoutErrorLabel("region_state");
        }

        return sizeof($error) == 0 ? true : $error;
    }

} 