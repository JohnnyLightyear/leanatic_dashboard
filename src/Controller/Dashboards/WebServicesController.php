<?php

namespace App\Controller\Dashboards;

use App\Service\Dashboard\WebService;
use App\Webhook\WsDashboardRequestParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebServicesController extends AbstractController
{
    #[Route('/dashboards/web-services', name: 'app_dashboards_webServices')]
    public function index(Request $request, WebService $webService): Response
    {
        if ($request->isXmlHttpRequest()) {
            return $this->render('dashboards/_partials/_info.html.twig', [
                'origins' => WsDashboardRequestParser::ORIGINS,
                'dashboardData' => $webService->format(),
            ]);
        }

        return $this->render('dashboards/web_services.html.twig', [
            'origins' => WsDashboardRequestParser::ORIGINS,
            'dashboardData' => $webService->format(),
        ]);
    }
}
