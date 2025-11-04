import { Routes } from '@angular/router';


export const routes: Routes = [
    {
        path: '',
        loadChildren: () =>
          import('./layout/layout.module').then((m) => m.LayoutModule),
    },   
    {
        path: 'error',
        loadChildren: () =>
          import('./errors/errors.module').then((m) => m.ErrorsModule),
    },      
    { path: '**', redirectTo: 'error' },     
];