<?php

namespace Ace\StaticBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ace\StaticBundle\Entity\BlogPost;

//use Ace\StaticBundle\Entity\Contact;
use Ace\StaticBundle\Entity\Prereg;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class developer
{
    public $name;
    public $image;
    public $description;

    function __construct($name, $subtext, $image, $description)
    {
        $this->name = $name;
        $this->subtext = $subtext;
        $this->image = $image;
        $this->description = $description;
    }
}

class DefaultController extends Controller
{

    public function aboutAction()
    {
        return $this->render('AceStaticBundle:Default:about.html.twig');
    }

    public function techAction()
    {
        return $this->render('AceStaticBundle:Default:tech.html.twig');
    }

    public function teamAction()
    {
        $dev_images_dir = "images/developers/";
        $tzikis_name = "Vasilis Georgitzikis";
        $tzikis_title = "teh lead";
        $tzikis_avatar = $dev_images_dir . "tzikis.jpeg";
        $tzikis_desc = "I am a student at the Computer Engineering and Informatics Department of the University of Patras, Greece, a researcher at the Research Academic Computer Technology Institute, and an Arduino and iPhone/OSX/Cocoa developer. Basically, just a geek who likes building stuff, which is what started codebender in the first place.";
        $tzikis = new developer($tzikis_name, $tzikis_title, $tzikis_avatar, $tzikis_desc);

        $tsampas_name = "Stelios Tsampas";
        $tsampas_title = "teh crazor";
        $tsampas_avatar = $dev_images_dir . "tsampas.png";
        $tsampas_desc = "Yet another student at CEID. My task is to make sure to bring crazy ideas to the table and let others assess their value. I'm also responsible for the Arduino Ethernet TFTP bootloader, the only crazy idea that didn't originate from me. I also have a 'wierd' coding style that causes much distress to $tzikis_name.";
        $tsampas = new developer($tsampas_name, $tsampas_title, $tsampas_avatar, $tsampas_desc);

        $amaxilatis_name = "Dimitris Amaxilatis";
        $amaxilatis_title = "teh code monkey";
        $amaxilatis_avatar = $dev_images_dir . "amaxilatis.jpg";
        $amaxilatis_desc = "Master Student at the Computer Engineering and Informatics Department of the University of Patras, Greece. Researcher at  the Research Unit 1 of Computer Technology Institute & Press (Diophantus) in the fields of Distributed Systems and Wireless Sensor Networks.";
        $amaxilatis = new developer($amaxilatis_name, $amaxilatis_title, $amaxilatis_avatar, $amaxilatis_desc);

        $kousta_name = "Maria Kousta";
        $kousta_title = "teh lady";
        $kousta_avatar = $dev_images_dir . "kousta.png";
        $kousta_desc = "A CEID graduate. My task is to develop the various parts of the site besides the core 'code and compile' page that make it a truly social-building website.";
        $kousta = new developer($kousta_name, $kousta_title, $kousta_avatar, $kousta_desc);

        $orfanos_name = "Markellos Orfanos";
        $orfanos_title = "teh fireman";
        $orfanos_avatar = $dev_images_dir . "orfanos.jpg";
        $orfanos_desc = "I am also (not for long I hope) a student at the Computer Engineering & Informatics Department and probably the most important person in the team. My task? Make sure everyone keeps calm and the team is having fun. And yes, I'm the one who developed our wonderful options page. Apart from that, I'm trying to graduate and some time in the future to become a full blown Gentoo developer.";
        $orfanos = new developer($orfanos_name, $orfanos_title, $orfanos_avatar, $orfanos_desc);

        $dimakopoulos_name = "Dimitris Dimakopoulos";
        $dimakopoulos_title = "teh awesome";
        $dimakopoulos_avatar = $dev_images_dir . "dimakopoulos.jpg";
        $dimakopoulos_desc = "Student at the Computer Engineering and Informatics Department of the University of Patras, Greece, have worked as an intern for Philips Consumer Lifestyle in Eindhoven and for the Research Academic Computer Technology Institute in Patras. Totally excited with Codebender as it combines web development and distributed systems, them being among my favorite fields.";
        $dimakopoulos = new developer($dimakopoulos_name, $dimakopoulos_title, $dimakopoulos_avatar, $dimakopoulos_desc);

        $christidis_name = "Dimitrios Christidis";
        $christidis_title = "teh bald guy";
        $christidis_avatar = $dev_images_dir . "christidis.jpg";
        $christidis_desc = "Currently a student and an assistant administrator. I am responsible for the compiler backend, ensuring that it's fast and robust.  Known as a perfectionist, I often fuss over coding style and documentation.";
        $christidis = new developer($christidis_name, $christidis_title, $christidis_avatar, $christidis_desc);

        $baltas_name = "Alexandros Baltas";
        $baltas_title = "teh artist";
        $baltas_avatar = $dev_images_dir . "baltas.png";
        $baltas_desc = "Guess what. I'm also a CEID undergraduate. And a drummer. When I'm not eating lots of food, I'm drinking lots of coffee and I can be found coding for codebender while distracting the rest of the team with my 'jokes'.";
        $baltas = new developer($baltas_name, $baltas_title, $baltas_avatar, $baltas_desc);

        $developers = array($tzikis, $amaxilatis, $orfanos, $christidis, $baltas);
        $friends = array($tsampas);
        $past = array($kousta, $dimakopoulos);
        return $this->render('AceStaticBundle:Default:team.html.twig', array("developers" => $developers, "friends" => $friends, "past" => $past));
    }

    public function tutorialsAction()
    {
        return $this->render('AceStaticBundle:Default:tutorials.html.twig');
    }

    public function walkthroughAction($page)
    {
        if (file_exists(__DIR__ . "/../Resources/views/Walkthrough/page" . intval($page) . ".html.twig")) {
            $this->get('ace_user.usercontroller')->setWalkthroughStatusAction(intval($page));
            return $this->render('AceStaticBundle:Walkthrough:page' . intval($page) . '.html.twig', array("page" => intval($page)));
            //return $this->render('this is a test', array("page" => intval($page)));
        }

        return $this->redirect($this->generateUrl("AceGenericBundle_index"));
    }

    public function documentationAction($page)
    {
        $page = trim(strtolower($page));
        $toshow = "";
        $fpath = $this->container->getParameter('wiselib.wiki.dir') . DIRECTORY_SEPARATOR;

        if ($page == "home") {
            if (file_exists($fpath . "Home.md")) {
                $toshow = file_get_contents($fpath . "Home.md");
            } else {
                $toshow = "<h2>Sorry</h2><p>The resource you are looking for is currently not available. Please contact the administrator for resolving this issue.</p>";
            }

            return $this->render('AceStaticBundle:Documentation:page.html.twig', array("dot_md_file" => $toshow));
        } else if ($page == "supported-platforms") {
            if (file_exists($fpath . "Supported-platforms.md")) {
                $toshow = file_get_contents($fpath . "Supported-platforms.md");
            } else {
                $toshow = "<h2>Sorry</h2><p>The resource you are looking for is currently not available. Please contact the administrator for resolving this issue.</p>";
            }
            return $this->render('AceStaticBundle:Documentation:page.html.twig', array("dot_md_file" => $toshow));
        } else if ($page == "programming-hints") {
            if (file_exists($fpath . "Programming-hints.md")) {
                $toshow = file_get_contents($fpath . "Programming-hints.md");
            } else {
                $toshow = "<h2>Sorry</h2><p>The resource you are looking for is currently not available. Please contact the administrator for resolving this issue.</p>";
            }
            return $this->render('AceStaticBundle:Documentation:page.html.twig', array("dot_md_file" => $toshow));
        } else if ($page == "coding-guidelines") {
            if (file_exists($fpath . "Coding-guidelines.md")) {
                $toshow = file_get_contents($fpath . "Coding-guidelines.md");
            } else {
                $toshow = "<h2>Sorry</h2><p>The resource you are looking for is currently not available. Please contact the administrator for resolving this issue.</p>";
            }
            return $this->render('AceStaticBundle:Documentation:page.html.twig', array("dot_md_file" => $toshow));
        } else if ($page == "generic-apps") {
            if (file_exists($fpath . "Generic-wiselib-application.md")) {
                $toshow = file_get_contents($fpath . "Generic-wiselib-application.md");
            } else {
                $toshow = "<h2>Sorry</h2><p>The resource you are looking for is currently not available. Please contact the administrator for resolving this issue.</p>";
            }
            return $this->render('AceStaticBundle:Documentation:page.html.twig', array("dot_md_file" => $toshow));
        } else if ($page == "background") {
            if (file_exists($fpath . "Background-general-cpp.md")) {
                $toshow = file_get_contents($fpath . "Background-general-cpp.md");
            } else {
                $toshow = "<h2>Sorry</h2><p>The resource you are looking for is currently not available. Please contact the administrator for resolving this issue.</p>";
            }
            return $this->render('AceStaticBundle:Documentation:page.html.twig', array("dot_md_file" => $toshow));
        } else if ($page == "reserved-msg-ids") {
            if (file_exists($fpath . "Reserved-message-ids.md")) {
                $toshow = file_get_contents($fpath . "Reserved-message-ids.md");
            } else {
                $toshow = "<h2>Sorry</h2><p>The resource you are looking for is currently not available. Please contact the administrator for resolving this issue.</p>";
            }
            return $this->render('AceStaticBundle:Documentation:page.html.twig', array("dot_md_file" => $toshow));
        }
        return $this->redirect($this->generateUrl("AceGenericBundle_index"));
    }

//	public function contactAction(Request $request)
//	{
//        // create a task and give it some dummy data for this example
//        $task = new Contact();
//		if ($this->get('security.context')->isGranted('ROLE_USER') === true)
//		{
//			$user = json_decode($this->get('ace_user.usercontroller')->getCurrentUserAction()->getContent(), true);
//	        $task->setName($user["firstname"]." ".$user["lastname"]." (".$user["username"].")");
//	        $task->setEmail($user["email"]);
//		}
//
//        $form = $this->createFormBuilder($task)
//            ->add('name', 'text')
//            ->add('email', 'email')
//            ->add('text', 'textarea')
//            ->getForm();
//
//		if ($request->getMethod() == 'POST')
//		{
//			$form->bindRequest($request);
//
//			if ($form->isValid())
//			{
//				$email_addr = $this->container->getParameter('email.addr');
//
//				// perform some action, such as saving the task to the database
//			    $message = \Swift_Message::newInstance()
//			        ->setSubject('codebender contact request')
//			        ->setFrom($email_addr)
//			        ->setTo($email_addr)
//			        ->setBody($this->renderView('AceStaticBundle:Default:contact_email_form.txt.twig', array('task' => $task)))
//			    ;
//			    $this->get('mailer')->send($message);
//				$this->get('session')->setFlash('notice', 'Your message was sent!');
//
//				return $this->redirect($this->generateUrl('AceStaticBundle_contact'));
//			}
//		}
//
//        return $this->render('AceStaticBundle:Default:contact.html.twig', array(
//            'form' => $form->createView(),
//        ));
//	}


    public function pluginAction()
    {
        return $this->render('AceStaticBundle:Default:plugin.html.twig', array());
    }

    public function partnerAction($name)
    {
        if (file_exists(__DIR__ . "/../Resources/views/Partner/" . $name . ".html.twig"))
            return $this->render('AceStaticBundle:Partner:' . $name . '.html.twig');

        return $this->redirect($this->generateUrl("AceGenericBundle_index"));
    }

    public function infoPointsAction()
    {
        return $this->render('AceStaticBundle:Default:info_points.html.twig', array());
    }

    public function infoKarmaAction()
    {
        return $this->render('AceStaticBundle:Default:info_karma.html.twig', array());
    }

    public function infoPrivateProjectsAction()
    {
        /** @var SketchController $projectmanager */
        $projectmanager = $this->get('ace_project.sketchmanager');

        $records = json_decode($projectmanager->currentPrivateProjectRecordsAction()->getContent(), true);

        return $this->render('AceStaticBundle:Default:info_private_projects.html.twig', array("records" => $records));
    }

    public function uploadBootloaderAction()
    {
        $programmers = array();

        $programmers[] = array(
            "name" => "USBtinyISP",
            "communication" => "",
            "protocol" => "usbtiny",
            "speed" => "0",
            "force" => "false"
        );
        $programmers[] = array(
            "name" => "AVR ISP",
            "communication" => "serial",
            "protocol" => "stk500v1",
            "speed" => "0",
            "force" => "false"
        );
        $programmers[] = array(
            "name" => "AVRISP mkII",
            "communication" => "usb",
            "protocol" => "stk500v2",
            "speed" => "0",
            "force" => "false"
        );
        $programmers[] = array(
            "name" => "USBasp",
            "communication" => "usb",
            "protocol" => "usbasp",
            "speed" => "0",
            "force" => "false"
        );
        $programmers[] = array(
            "name" => "Parallel Programmer",
            "communication" => "dapa",
            "protocol" => "",
            "speed" => "0",
            "force" => "true"
        );
        $programmers[] = array(
            "name" => "Arduino as ISP",
            "communication" => "serial",
            "protocol" => "stk500v1",
            "speed" => "19200",
            "force" => "false"
        );

        return $this->render('AceStaticBundle:Default:upload_bootloader.html.twig', array("programmers" => $programmers));
    }
}
