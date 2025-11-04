import { Routes } from '@angular/router';

const Routing: Routes = [    
    {
      path: 'home',
      title: 'nav.homeNav',
      data: {
        animation: 'home',
        reuseComponent: false
      },       
      loadChildren: () => import('./home/home.module').then((m) => m.HomeModule),
    },   
    {
      path: 'technology',
      title: 'nav.technologyNav',
      data: {
        animation: 'technology',
        reuseComponent: false
      },       
      loadChildren: () => import('./technology/technology.module').then((m) => m.TechnologyModule),
    },
    {
      path: 'company',
      title: 'nav.companyNav',
      data: {
        animation: 'company',
        reuseComponent: false
      },       
      loadChildren: () => import('./company/company.module').then((m) => m.CompanyModule),
    },  
    {
      path: 'team',
      title: 'nav.teamNav',
      data: {
        animation: 'team',
        reuseComponent: false
      },       
      loadChildren: () => import('./team/team.module').then((m) => m.TeamModule),
    },   
    {
      path: 'partners',
      title: 'nav.partnersNav',
      data: {
        animation: 'partner',
        reuseComponent: false
      },       
      loadChildren: () => import('./partners/partners.module').then((m) => m.PartnersModule),
    },       
    {
      path: 'blog',
      title: 'nav.blogNav',
      data: {
        animation: 'blog',
        reuseComponent: false
      },       
      loadChildren: () => import('./blog/blog.module').then((m) => m.BlogModule),
    },      
    {
      path: 'contact',
      title: 'nav.contactNav',
      data: {
        animation: 'contact',
        reuseComponent: false
      },       
      loadChildren: () => import('./contact/contact.module').then((m) => m.ContactModule),
    },      
    {
      path: 'tdu',
      title: 'nav.privacyNav',
      data: {
        animation: 'privacy',
        reuseComponent: false
      },       
      loadChildren: () => import('./tou/tou.module').then((m) => m.TouModule),
    },                   
    {
        path: '',
        redirectTo: '/home',
        pathMatch: 'full',
    }   
]
export { Routing };