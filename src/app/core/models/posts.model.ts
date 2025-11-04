import { Category } from './category.model';
import { Pictures } from './pictures.model';

import { BaseModel } from '../../shared/helpers';

export interface Posts extends BaseModel {
    id: number;
    name: string | any;
    description: string;
    long_description: string;
    folder: string;
    date: string;
    order?: number;
    slug: string;
    external_link?: string | any;
    categories?: Category[];
    pictures?: Pictures | any;
    language?: Pictures | any;
    total?: number;
    time_read?: {
        minutes: number,
        seconds: number,
    };
}