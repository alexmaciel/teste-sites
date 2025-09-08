import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';

import { TeamService } from '../../core';

@Component({
  selector: 'app-team',
  templateUrl: './team.component.html'
})
export class TeamComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription[] = [];

  constructor(
    // Services
    public teams: TeamService
  ) {
    if (typeof document !== 'undefined') {
      document.body.setAttribute('data-mv-app-header-color', 'color');
    } 
  }

  ngOnInit(): void {
    const sb = this.teams.getTeam().pipe(
    ).subscribe();
    this.subscriptions.push(sb);      
  }

  ngOnDestroy(): void {
    if (typeof document !== 'undefined') {
      document.body.removeAttribute('data-mv-app-header-color');
    }     
  }
}
