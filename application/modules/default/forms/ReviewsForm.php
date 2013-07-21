<?php
class Default_Form_ReviewsForm extends Zend_Form
{
    
    public function init()
    {
        // Set the custom decorator
        $this->addElementPrefixPath('Shineisp_Decorator', 'Shineisp/Decorator/', 'decorator');
        
        $this->addElement('text', 'nick', array(
            'filters'     => array('StringTrim'),
            'required'    => true,
        	'description' => 'Add your own nickname',
            'decorators'  => array('Composite'),
            'label'       => 'Nick',
            'class'       => 'text-input medium-input'
        ));
                  
        $this->addElement('text', 'subject', array(
            'filters'     => array('StringTrim'),
            'required'    => false,
            'decorators'  => array('Composite'),
        	'description' => 'Write down a subject of the review',
            'label'       => 'Subject',
            'class'       => 'text-input large-input'
        ));
                  
        $this->addElement('select', 'referer', array(
            'filters'     => array('StringTrim'),
            'decorators'  => array('Composite'),
        	'description' => 'Where did you find us?',
            'label'       => 'Who is Talking About Us?',
            'class'       => 'text-input medium-input',
        	'multiOptions' => array('Google' => 'Google', 'Bing' => 'Bing', 'Yahoo' => 'Yahoo', 'Other Search Engine' => 'Other Search Engine', 'Websites' => 'Websites/Blogs', 'Magento Commerce' => 'Magento Commerce', 'Friend suggestion' => 'Friend suggestion')
        ));
                  
        $this->addElement('text', 'city', array(
            'filters'     => array('StringTrim'),
            'decorators'  => array('Composite'),
        	'description' => 'Which is your own city? If added we will promote your review in our website using Google Maps',
            'label'       => 'City',
            'class'       => 'text-input medium-input'
        ));
                  
        $this->addElement('text', 'email', array(
            'filters'     => array('StringTrim'),
            'required'    => true,
        	'validators' => array(
                'EmailAddress'
            ),
            'decorators'  => array('Composite'),
        	'description' => 'Your email will be not published',
            'label'       => 'Email',
            'class'       => 'text-input medium-input'
        ));
        
    	$this->addElement('select', 'stars', array(
            'filters'    => array('StringTrim'),
            'required'   => true,
            'label'      => 'Stars',
            'decorators' => array('Composite'),
            'class'      => 'text-input medium-input',
    		'multiOptions' => array(1 => '1 Star', 2 => '2 Stars', 3 => '3 Stars', 4 => '4 Stars', 5 => '5 Stars')
        ));        
        
        $this->addElement('textarea', 'review', array(
            'filters'     => array('StringTrim'),
            'decorators'  => array('Composite'),
        	'required'    => true,
        	'description' => 'Write down your review with details and you will earn points and discounts',
            'label'       => 'Review',
            'class'       => 'textarea'
        ));
        
        $privKey = Settings::findbyParam('recaptcha_private_key');
        $pubKey = Settings::findbyParam('recaptcha_public_key');
        
        if(!empty($pubKey) && !empty($privKey)){
        
        	$recaptcha = new Zend_Service_ReCaptcha($pubKey, $privKey);
        	$captcha = new Zend_Form_Element_Captcha('captcha',
        			array(
        					'label' => 'Captcha Check',
        					'description' => 'Type the characters you see in the picture below.',
        					'captcha' =>  'ReCaptcha',
        					'captchaOptions'        => array(
        							'captcha'   => 'ReCaptcha',
        							'service'   => $recaptcha
        					)
        			)
        	);
        
        	$this->addElement($captcha);
        }else{

       		$captcha = new Zend_Form_Element_Captcha(
        				'captcha', // This is the name of the input field
        				array('label' => 'Write the chars to the field',
        						'captcha' => array( // Here comes the magic...
        								// First the type...
        								'captcha' => 'Image',
        								// Length of the word...
        								'wordLen' => 6,
        								// Captcha timeout, 5 mins
        								'timeout' => 300,
        								// What font to use...
        								'font' => PUBLIC_PATH . '/resources/fonts/arial.ttf',
        								// Where to put the image
        								'imgDir' => PUBLIC_PATH . '/tmp',
        								// URL to the images
        								// This was bogus, here's how it should be... Sorry again :S
        								'imgUrl' => '/tmp/',
        						)));
        	$this->addElement($captcha);
        }
   
        $this->addElement('submit', 'save', array(
            'required' => false,
            'label'    => 'Publish your Review',
            'decorators' => array('Composite'),
            'class'    => 'button'
        ));
        
        $this->addElement('hidden', 'product_id');

    }
    
}