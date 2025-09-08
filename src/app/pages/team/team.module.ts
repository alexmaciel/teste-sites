import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TeamRoutingModule } from './team-routing.module';
import { TeamComponent } from './team.component';

import { 
  NgxOptimizedImageModule
 } from '../../shared';

import { CoreModule } from '../../core';

@NgModule({
  declarations: [
    TeamComponent
  ],
  imports: [
    CommonModule,
    TeamRoutingModule,
    // Plugins
    NgxOptimizedImageModule,
    CoreModule
  ]
})
export class TeamModule { }
