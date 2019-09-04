<?php
// ImageType
 
namespace App\Form;
  
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Entity\Image;
  
class ImageType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            
            ->add('file', FileType::class, ['label' => 'Ajouter un image'])
			//->add('url')
			//->add('alt');
			;
    }
	
	
  public function configureOptions(OptionsResolver $resolver)

  {

    $resolver->setDefaults(array(

      'data_class' => Image::class,
	  //'data_class' => null,

    ));

  }
  public function __toString()
    {
      return $this->getUrl();
    }
}