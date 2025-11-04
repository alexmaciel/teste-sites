import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';


import { 
  CompanyService,
  CompanyItemService,
  TeamService,
} from '../../core';

@Component({
  selector: 'app-company',
  templateUrl: './company.component.html'
})
export class CompanyComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription[] = [];

  constructor(
    // Services
    public company: CompanyService,
    public items: CompanyItemService,
    public teams: TeamService
  ) {}   

  ngOnInit(): void {
    this.loadCompany();
    this.loadItems();
    this.loadTeams();
  }  
    
  loadCompany() {
    const sb = this.company.getCompany().pipe(
    ).subscribe();
    this.subscriptions.push(sb);   
  }  

  loadItems() {
    const sb = this.items.getItems().pipe(
    ).subscribe();
    this.subscriptions.push(sb);       
  }  

  loadTeams() {
    const sb = this.teams.getTeam().pipe(
    ).subscribe();
    this.subscriptions.push(sb);      
  }
  
  ngOnDestroy(): void {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
  }  
}
