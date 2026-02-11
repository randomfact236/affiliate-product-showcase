import { PartialType } from '@nestjs/swagger';
import { CreateRibbonDto } from './create-ribbon.dto';

export class UpdateRibbonDto extends PartialType(CreateRibbonDto) {}
