import { Routes } from '@angular/router';

const Routing: Routes = [    
    {
      path: 'home',
      title: 'nav.homeNav',
      data: {
        animation: 'home',
        reuseComponent: true
      },       
      loadChildren: () => import('./home/home.module').then((m) => m.HomeModule),
    },   
    {
      path: 'technology',
      title: 'nav.technologyNav',
      data: {
        animation: 'technology',
        reuseComponent: true
      },       
      loadChildren: () => import('./technology/technology.module').then((m) => m.TechnologyModule),
    },
    {
      path: 'company',
      title: 'nav.companyNav',
      data: {
        animation: 'company',
        reuseComponent: true
      },       
      loadChildren: () => import('./company/company.module').then((m) => m.CompanyModule),
    },  
    {
      path: 'team',
      title: 'nav.teamNav',
      data: {
        animation: 'team',
        reuseComponent: true
      },       
      loadChildren: () => import('./team/team.module').then((m) => m.TeamModule),
    },   
    {
      path: 'blog',
      title: 'nav.blogNav',
      data: {
        animation: 'blog',
        reuseComponent: true
      },       
      loadChildren: () => import('./blog/blog.module').then((m) => m.BlogModule),
    },      
    {
      path: 'contact',
      title: 'nav.contactNav',
      data: {
        animation: 'contact',
        reuseComponent: true
      },       
      loadChildren: () => import('./contact/contact.module').then((m) => m.ContactModule),
    },                    
    {
        path: '',
        redirectTo: '/home',
        pathMatch: 'full',
    }   
]
export { Routing };