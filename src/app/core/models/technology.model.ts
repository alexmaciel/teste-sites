import { Pictures } from './pictures.model';

export interface Technology {
    name: string;
    description: string;
    long_description: string;
    folder: string;  
    pictures?: Pictures;
    language?: string;
}