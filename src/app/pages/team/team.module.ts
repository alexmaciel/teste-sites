import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TeamRoutingModule } from './team-routing.module';
import { TeamComponent } from './team.component';

import { 
  NgxOptimizedImageModule,
  SharedModule 
} from '../../shared';

@NgModule({
  declarations: [
    TeamComponent
  ],
  imports: [
    CommonModule,
    TeamRoutingModule,
    // Plugins
    NgxOptimizedImageModule,
    SharedModule
  ]
})
export class TeamModule { }
